<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Metadatable;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
interface MetadatableInterface
{
    /**
     * @return MetadataInterface[]
     */
    public function getMetadata();

    /**
     * @param array|\Traversable|MetadataInterface $metadata
     */
    public function setMetadata($metadata);

    /**
     * @param array|\Traversable|MetadataInterface $metadata
     */
    public function addMetadata($metadata);

    /**
     * @param array|\Traversable|MetadataInterface $metadata
     */
    public function removeMetadata($metadata);

    /**
     * Removes all metadata
     */
    public function clearMetadata();
}
