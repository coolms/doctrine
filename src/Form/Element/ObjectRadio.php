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

use DoctrineModule\Form\Element\ObjectRadio as BaseObjectRadio;

class ObjectRadio extends BaseObjectRadio
{
    use ObjectElementTrait;

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        return parent::setValue($this->getValueOption($value));
    }
}
