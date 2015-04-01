<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Changeable extends Annotation
{
    public $field;

    public $value;
}
