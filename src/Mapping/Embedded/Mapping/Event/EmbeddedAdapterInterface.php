<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Embedded\Mapping\Event;

use Gedmo\Mapping\Event\AdapterInterface,
    Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Doctrine event adapter interface
 * for EmbeddedOverride behavior
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
interface EmbeddedAdapterInterface extends AdapterInterface
{
    /**
     * @param ClassMetadata $meta
     * @param array         $fieldOverrides
     *
     * @return void
     */
    public function mapFields($meta, array $fieldOverrides);
}
