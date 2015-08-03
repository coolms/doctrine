<?php
/**
 * CoolMS2 Doctrine Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Form;

use Zend\Form\Exception,
    Zend\Form\FieldsetInterface,
    Zend\Form\FormElementManager,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\Common\Persistence\ObjectManagerAware,
    DoctrineModule\Persistence\ObjectManagerAwareInterface,
    DoctrineModule\Persistence\ProvidesObjectManager,
    CmsCommon\Form\Factory as BaseFactory;

class Factory extends BaseFactory implements ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    /**
     * {@inheritDoc}
     *
     * @param ObjectManager
     */
    public function __construct(FormElementManager $formElementManager = null, ObjectManager $objectManager = null)
    {
        parent::__construct($formElementManager);
        if ($objectManager) {
            $this->setObjectManager($objectManager);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareAndInjectObject($objectName, FieldsetInterface $fieldset, $method)
    {
        if (!is_string($objectName)) {
            throw new Exception\DomainException(sprintf(
                '%s expects string class name; received "%s"',
                $method,
                (is_object($objectName) ? get_class($objectName) : gettype($objectName))
            ));
        }

        if (!class_exists($objectName)) {
            throw new Exception\DomainException(sprintf(
                '%s expects string class name to be a valid class name; received "%s"',
                $method,
                $objectName
            ));
        }

        $om = null;
        if ($fieldset instanceof ObjectManagerAwareInterface) {
            $om = $fieldset->getObjectManager();
        }

        if (!$om) {
            $om = $this->getObjectManager();
        }

        $object = new $objectName;
        if ($om) {
            /* @var $cm \Doctrine\Common\Persistence\Mapping\ClassMetadata */
            if ($object instanceof ObjectManagerAware && $om->getMetadataFactory()->isTransient($objectName)) {
                $cm = $om->getClassMetadata($objectName);
                $object->injectObjectManager($om, $cm);
            } elseif ($object instanceof ObjectManagerAwareInterface) {
                $object->setObjectManager($om);
            }
        }

        $fieldset->setObject($object);
    }
}
