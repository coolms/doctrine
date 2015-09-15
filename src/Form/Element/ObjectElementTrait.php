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

use CmsDoctrine\Mapping\Translatable\TranslatableInterface;

trait ObjectElementTrait
{
    /**
     * @return Proxy
     */
    public function getProxy()
    {
        if (null === $this->proxy) {
            $this->proxy = new Proxy();
        }

        return $this->proxy;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setFindMethodParam($name, $value)
    {
        $proxy = $this->getProxy();
        $proxy->clearValueOptions();
        $findMethod = $proxy->getFindMethod();
        $findMethod['params'][$name] = $value;
        $proxy->setFindMethod($findMethod);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getFindMethodParam($name)
    {
        $findMethod = $this->getProxy()->getFindMethod();
        if (isset($findMethod['params'][$name])) {
            return $findMethod['params'][$name];
        }
    }

    /**
     * @param  array $options
     * @return Select
     */
    public function setValueOptions(array $options)
    {
        $targetClass = $this->getProxy()->getTargetClass();
        $targetClass = $this->getProxy()->getObjectManager()->getClassMetadata($targetClass)->getName();

        if (!empty($options) && is_a($targetClass, TranslatableInterface::class, true)) {
            foreach ($options as $key => $optionSpec) {
                if (is_array($optionSpec)) {
                    $options[$key]['translator_disabled'] = true;
                }
            }
        }

        return parent::setValueOptions($options);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function getValueOption($value)
    {
        $proxy = $this->getProxy();

        $valueOption  = $proxy->getValue($value);
        $valueOptions = $this->getValueOptions();

        if (!array_filter($valueOptions, function($option) use ($valueOption) {
            return $option['value'] == $valueOption;
        })) {
            $proxy->clearValueOptions();
            $proxyValueOptions = $proxy->getValueOptions();

            if (!empty($proxyValueOptions)) {
                $this->setValueOptions($proxyValueOptions);
            }
        }

        return $valueOption;
    }
}
