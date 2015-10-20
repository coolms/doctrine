<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Session;

use Zend\Session\Container as ZendSessionContainer,
    Zend\Session\ManagerInterface as Manager,
    Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\ObjectManager,
    DoctrineModule\Persistence\ProvidesObjectManager,
    Gedmo\Mapping\Event\AdapterInterface;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class Container extends ZendSessionContainer
{
    use ProvidesObjectManager;

    /**
     * @var array
     */
    private $sessionVars = [];

    /**
     * Event adapters
     *
     * @var array
     */
    private $adapters = [];

    /**
     * {@inheritDoc}
     *
     * @param ObjectManager $objectManager
     */
    public function __construct($name, Manager $manager = null, ObjectManager $objectManager)
    {
        parent::__construct($name, $manager);
        $this->setObjectManager($objectManager);
        $objectManager->getEventManager()->addEventListener(['onFlush'], $this);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value)
    {
        $class = $this->getName();
        $om = $this->getObjectManager();
        if (is_a($value, $class, true) && !$om->getMetadataFactory()->isTransient(get_class($value))) {
            if ($om->contains($value)) {
                $om->detach($value);
            }

            $this->sessionVars[$key] = $value;
        }

        parent::offsetSet($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function &offsetGet($key)
    {
        if (isset($this->sessionVars[$key])) {
            $value = $this->sessionVars[$key];
        } else {
            $value = parent::offsetGet($key);
        }

        $class = $this->getName();
        $om = $this->getObjectManager();
        if (is_a($value, $class, true) && !$om->getMetadataFactory()->isTransient(get_class($value))) {
            if (!$om->contains($value)) {
                $value = $om->merge($value);
            }

            $this->sessionVars[$key] = $value;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($key)
    {
        if (!empty($this->sessionVars[$key])) {
            return true;
        }

        return parent::offsetExists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($key)
    {
        if (isset($this->sessionVars[$key])) {
            unset($this->sessionVars[$key]);
        }

        parent::offsetUnset($key);
    }

    /**
     * @param EventArgs $args
     */
    public function onFlush(EventArgs $args)
    {
        if ($this->sessionVars) {
            $ea = $this->getEventAdapter($args);
            $om = $this->getObjectManager();
            $uow = $om->getUnitOfWork();

            if ($objects = $ea->getScheduledObjectInsertions($uow)) {
                $this->sessionVars = array_filter($this->sessionVars, function($object) use ($objects) {
                    return !in_array($object, $objects, true);
                });
            }
        }
    }

    /**
     * Get an event adapter to handle event specific methods
     *
     * @param EventArgs $args
     * @throws \InvalidArgumentException - if event is not recognized
     * @return AdapterInterface
     */
    protected function getEventAdapter(EventArgs $args)
    {
        $class = get_class($args);
        if (preg_match('@Doctrine\\\([^\\\]+)@', $class, $m) && in_array($m[1], ['ODM', 'ORM'])) {
            if (!isset($this->adapters[$m[1]])) {
                $adapterClass = 'Gedmo\\Mapping\\Event\\Adapter\\' . $m[1];
                $this->adapters[$m[1]] = new $adapterClass();
            }

            $this->adapters[$m[1]]->setEventArgs($args);
            return $this->adapters[$m[1]];
        }

        throw new \InvalidArgumentException('Session continaer does not support event arg class: ' . $class);
    }
}
