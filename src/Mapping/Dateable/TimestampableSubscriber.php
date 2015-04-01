<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable;

use Doctrine\Common\NotifyPropertyChanged,
    Gedmo\Timestampable\TimestampableListener;

/**
 * The Timestampable listener handles the update of
 * dates on creation and update.
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class TimestampableSubscriber extends TimestampableListener
{
    /**
     * Changeable annotation class
     */
    const CHANGEABLE_ANNOTATION = 'CmsDoctrine\\Mapping\\Dateable\\Annotation\\Changeable';

    /**
     * Changeable document annotation alias
     */
    const CHANGEABLE_ODM_ANNOTATION_ALIAS = 'Doctrine\\ODM\\Mapping\\Changeable';

    /**
     * Changeable entity annotation alias
     */
    const CHANGEABLE_ORM_ANNOTATION_ALIAS = 'Doctrine\\ORM\\Mapping\\Changeable';

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        if (!class_exists(self::CHANGEABLE_ODM_ANNOTATION_ALIAS)) {
            class_alias(self::CHANGEABLE_ANNOTATION, self::CHANGEABLE_ODM_ANNOTATION_ALIAS);
        }

        if (!class_exists(self::CHANGEABLE_ORM_ANNOTATION_ALIAS)) {
            class_alias(self::CHANGEABLE_ANNOTATION, self::CHANGEABLE_ORM_ANNOTATION_ALIAS);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritDoc}
     */
    protected function updateField($object, $ea, $meta, $field)
    {
        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);
        $newValue = $ea->getDateValue($meta, $field);

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
}
