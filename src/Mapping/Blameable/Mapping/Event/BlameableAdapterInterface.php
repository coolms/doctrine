<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Blameable\Mapping\Event;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Blameable\Mapping\Event\BlameableAdapter;

/**
 * Doctrine event adapter interface for Blameable behavior.
 */
interface BlameableAdapterInterface extends BlameableAdapter
{
    /**
     * @param ClassMetadata $meta
     * @param array $fields
     * @param string $targetEntity
     */
    public function remapFieldsToAssociations($meta, array $fields, $targetEntity);

    /**
     * @param ClassMetadata $meta
     * @param array $fields
     */
    public function remapAssociationsToFields($meta, array $fields);
}
