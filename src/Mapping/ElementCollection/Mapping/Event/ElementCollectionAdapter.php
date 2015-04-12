<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection\Mapping\Event;

use Doctrine\Common\Collections\Collection,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\Event\AdapterInterface;

/**
 * Doctrine event adapter interface for ORM adapted for ElementCollection behavior
 */
interface ElementCollectionAdapter extends AdapterInterface
{
    /**
     * @param ClassMetadata $meta
     * @param string $field
     * @param string $class
     * @return Collection
     */
    public function getElementCollection($meta, $field, $class);
}
