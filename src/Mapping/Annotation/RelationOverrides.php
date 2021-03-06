<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * This annotation is used to override association mappings of relationship properties.
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 *
 * @Annotation
 * @Target("CLASS")
 */
class RelationOverrides extends Annotation
{
    /**
     * Mapping overrides of relationship properties.
     *
     * @var array<\CmsDoctrine\Mapping\Annotation\RelationOverride>
     */
    public $value;
}
