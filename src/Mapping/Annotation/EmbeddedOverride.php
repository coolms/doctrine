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
 * This annotation is used to override the mapping of an embedded object property.
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class EmbeddedOverride extends Annotation
{
    /**
     * The name of the property whose mapping is being overridden.
     *
     * @var string 
     */
    public $name;

    /**
     * The column definition.
     *
     * @var \Doctrine\ORM\Mapping\Column
     */
    public $column;
}
