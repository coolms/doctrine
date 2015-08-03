<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ORM as EventAdapter,
    Gedmo\Timestampable\Mapping\Event\TimestampableAdapter;

/**
 * Doctrine event adapter for ORM adapted for Dateable behavior
 */
class ORM extends EventAdapter implements TimestampableAdapter
{
    /**
     * {@inheritDoc}
     */
    public function getDateValue($meta, $field)
    {
        $mapping = $meta->getFieldMapping($field);
        if (isset($mapping['type']) && $mapping['type'] === 'integer') {
            return time();
        }

        return new \DateTime();
    }

    /**
     * Overriden. Added support for ManyToMany association changes
     *
     * {@inheritDoc}
     */
    public function getObjectChangeSet($uow, $object)
    {
        $changeSet = parent::getObjectChangeSet($uow, $object);
        $meta = $this->getObjectManager()->getClassMetadata(get_class($object));
        $refl = $meta->getReflectionClass();
        $updates = $uow->getScheduledCollectionUpdates();
        $delitions = $uow->getScheduledCollectionDeletions();
        foreach ($meta->getAssociationNames() as $name) {
            if ($meta->isSingleValuedAssociation($name)) {
                continue;
            }

            $property = $refl->getProperty($name);
            $property->setAccessible(true);
            $assoc = $property->getValue($object);
            if (in_array($assoc, $updates, true) || in_array($assoc, $delitions, true)) {
                $changeSet[$name] = [$assoc, $assoc];
            }
        }

        return $changeSet;
    }
}
