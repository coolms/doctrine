<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection\Mapping\Event\Adapter;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\PersistentCollection,
    Gedmo\Mapping\Event\Adapter\ORM as GedmoORMAdapter,
    Gedmo\Timestampable\Mapping\Event\TimestampableAdapter,
    CmsDoctrine\Mapping\ElementCollection\Mapping\Event\ElementCollectionAdapter;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\Common\Collections\Criteria;

/**
 * Doctrine event adapter for ORM adapted for ElementCollection behavior
 */
final class ORM extends GedmoORMAdapter implements ElementCollectionAdapter
{
    /**
     * {@inheritDoc}
     */
    public function getElementCollection($meta, $field, $class, $entity)
    {
        $om = $this->getObjectManager();
        //$collection = new ArrayCollection($om->getRepository($class)->findAll());
        $collection = new ArrayCollection();
        //return new PersistentCollection($om, $class, $collection);

        /*$collection = new LazyCriteriaCollection(
            new BasicEntityPersister($om, $om->getClassMetadata($class)),
            new Criteria()
        );*/

        //return $collection;

        //echo $class;
        
        $coll = new PersistentCollection($om, $class, $collection);
        //$coll->setOwner($entity, $meta->getAssociationMapping($field));

        //echo $coll->count();

        return $coll;
    }
}
