<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ORM as GedmoORMAdapter,
    Gedmo\Timestampable\Mapping\Event\TimestampableAdapter;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use CmsDoctrine\Mapping\ElementCollection\Mapping\Event\ElementCollectionAdapter;

/**
 * Doctrine event adapter for ORM adapted for ElementCollection behavior
 */
final class ORM extends GedmoORMAdapter implements ElementCollectionAdapter
{
    /**
     * {@inheritDoc}
     */
    public function getElementCollection($meta, $field, $class)
    {
        $om = $this->getObjectManager();
        $collection = new ArrayCollection();

        return new PersistentCollection($om, $class, $collection);
    }
}
