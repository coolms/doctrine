<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
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

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
