<?php
/**
 * CoolMS2 Doctrine Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Form\Element;

use DoctrineModule\Form\Element\Proxy as BaseProxy;

class Proxy extends BaseProxy
{
    /**
     * @return self
     */
    public function clearValueOptions()
    {
        $this->objects = [];
        $this->valueOptions = [];
        return $this;
    }
}
