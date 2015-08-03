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

use Traversable,
    Zend\Stdlib\ArrayUtils,
    DoctrineModule\Form\Element\BaseObjectMultiCheckbox;

class ObjectMultiCheckbox extends BaseObjectMultiCheckbox
{
    use ObjectElementTrait;

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        if ($value instanceof Traversable) {
            $value = ArrayUtils::iteratorToArray($value);
        } elseif ($value == null) {
            return parent::setValue([]);
        } elseif (!is_array($value)) {
            $value = (array)$value;
        }

        return parent::setValue(array_map([$this, 'getValueOption'], $value));
    }
}
