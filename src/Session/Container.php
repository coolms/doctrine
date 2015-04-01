<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Session;

use Zend\Session\Container as ZendSessionContainer,
    Zend\Session\ManagerInterface as Manager,
    Doctrine\Common\Persistence\Event\LifecycleEventArgs,
    Doctrine\Common\Persistence\ObjectManager,
    DoctrineModule\Persistence\ProvidesObjectManager;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class Container extends ZendSessionContainer
{
    use ProvidesObjectManager;

    /**
     * @var array
     */
    private $sessionedObjects = [];

    /**
     * {@inheritDoc}
     *
     * @param ObjectManager $objectManager
     */
    public function __construct($name, Manager $manager = null, ObjectManager $objectManager)
    {
        parent::__construct($name, $manager);
        $this->setObjectManager($objectManager);
        $objectManager->getEventManager()->addEventListener(['postFlush'], $this);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value)
    {
        $class = $this->getName();
        if ($value instanceof $class) {
            $om = $this->getObjectManager();
            unset($this->sessionedObjects[$key]);
            $om->contains($value) ? $om->detach($value) : $om->persist($value);
        }

        parent::offsetSet($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function &offsetGet($key)
    {
        if (!empty($this->sessionedObjects[$key])) {
            return $this->sessionedObjects[$key];
        }

        $om    = $this->getObjectManager();
        $value = parent::offsetGet($key);
        $class = $this->getName();

        if ($value instanceof $class) {
            //$om->clear($class);
            if (!$om->contains($value)) {
                $value = $om->merge($value);
            }
            $this->sessionedObjects[$key] = $value;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($key)
    {
        if (!empty($this->sessionedObjects[$key])) {
            return true;
        }

        return parent::offsetExists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($key)
    {
        if (isset($this->sessionedObjects[$key])) {
            unset($this->sessionedObjects[$key]);
        }

        parent::offsetUnset($key);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postFlush(LifecycleEventArgs $args)
    {
        if ($this->sessionedObjects) {
            $object = $args->getObject();
            foreach ($this->sessionedObjects as $key => $sessionedObject) {
                if ($object === $sessionedObject) {
                    unset($this->sessionedObjects[$key]);
                }
            }
        }
    }
}
