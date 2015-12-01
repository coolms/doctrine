<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Blameable;

use Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\EventArgs as LoadClassMetadataEventArgs,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Blameable\BlameableListener,
    CmsDoctrine\Mapping\Blameable\Mapping\Event\BlameableAdapterInterface,
    CmsDoctrine\Mapping\Dateable\OnFlushTrait;

/**
 * The Blameable subscriber.
 *
 * @author  Dmitry Popov <d.popov@altgraphic.com>
 */
class BlameableSubscriber extends BlameableListener
{
    use OnFlushTrait;

    /**
     * @var string
     */
    protected $userClass;

    /**
     * __construct
     *
     * @param string $userClass
     */
    public function __construct($userClass = null)
    {
        parent::__construct();
        $this->setUserClass($userClass);
    }

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        parent::loadClassMetadata($eventArgs);

        $meta = $eventArgs->getClassMetadata();

        if (isset(static::$configurations[$this->name][$meta->getName()]['blames'])) {
            $ea = $this->getEventAdapter($eventArgs);
            $this->remap($ea, $meta, static::$configurations[$this->name][$meta->getName()]['blames']);
        }
    }

    /**
     * @param BlameableAdapterInterface $ea
     * @param ClassMetadata $meta
     * @param array $fields
     */
    protected function remap(BlameableAdapterInterface $ea, ClassMetadata $meta, array $fields)
    {
        if ($fields) {
            if ($this->getUserClass()) {
                $ea->remapFieldsToAssociations($meta, $fields, $this->getUserClass());
            } else {
                $ea->remapAssociationsToFields($meta, $fields);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setUserValue($user)
    {
        if (is_object($user) && $this->getUserClass() && !is_a($user, $this->getUserClass())) {
            throw new InvalidArgumentException(sprintf(
                'User value must be an instance of %s; %s given',
                $this->getUserClass(),
                get_class($user)
            ));
        }

        parent::setUserValue($user);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserValue($meta, $field)
    {
        if ($meta->hasAssociation($field)) {
            if (null !== $this->user && !is_object($this->user)) {
                throw new InvalidArgumentException('Blame is reference, user must be an object');
            }

            return $this->user;
        }

        // ok so its not an association, then it is a string
        if (is_object($this->user)) {
            if (method_exists($this->user, 'getId')) {
                return (string) $this->user->getId();
            }

            if (method_exists($this->user, 'getUsername')) {
                return (string) $this->user->getUsername();
            }

            if (method_exists($this->user, '__toString')) {
                return $this->user->__toString();
            }

            throw new InvalidArgumentException('Field expects string, user must be a string, ' .
                'or object should have method getId, getUsername or __toString');
        }

        return $this->user;
    }

    /**
     * @param string $className
     */
    public function setUserClass($className)
    {
        $this->userClass = $className;
    }

    /**
     * @return string
     */
    public function getUserClass()
    {
        if (!$this->userClass && is_object($this->user)) {
            $this->userClass = get_class($this->user);
        }

        return $this->userClass;
    }

    /**
     * {@inheritDoc}
     */
    protected function updateField($object, $ea, $meta, $field)
    {
        $newValue = $this->getUserValue($meta, $field);
        if (null === $newValue) {
            return;
        }

        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);

        //if blame is reference, persist object
        if ($meta->hasAssociation($field)) {
            $ea->getObjectManager()->persist($newValue);
        }

        $setter = 'set' . ucfirst($field);
        if (method_exists($object, $setter)) {
            $object->$setter($newValue);
        } else {
            $property->setValue($object, $newValue);
        }

        if ($object instanceof NotifyPropertyChanged) {
            $uow = $ea->getObjectManager()->getUnitOfWork();
            $uow->propertyChanged($object, $field, $oldValue, $newValue);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
