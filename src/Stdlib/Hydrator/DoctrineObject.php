<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Stdlib\Hydrator;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as BaseDoctrineObject;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class DoctrineObject extends BaseDoctrineObject
{
    /**
     * {@inheritDoc}
     */
    protected function extractByValue($object)
    {
        $fieldNames = $this->metadata->getFieldNames();
        $methods    = get_class_methods($object);
        $filter     = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = parent::extractByValue($object);
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName) || !strpos($fieldName, '.')) {
                continue;
            }

            list($fieldName) = explode('.', $fieldName, 2);
            $getter = 'get' . ucfirst($fieldName);

            $dataFieldName = $this->computeExtractFieldName($fieldName);
            if (in_array($getter, $methods)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$getter(), $object);
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function extractByReference($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $refl       = $this->metadata->getReflectionClass();
        $filter     = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = parent::extractByReference($object);
        foreach ($fieldNames as $fieldName) {
            if ($filter && !$filter->filter($fieldName) || !strpos($fieldName, '.')) {
                continue;
            }

            list($fieldName) = explode('.', $fieldName, 2);
            $reflProperty = $refl->getProperty($fieldName);
            $reflProperty->setAccessible(true);

            $dataFieldName = $this->computeExtractFieldName($fieldName);
            $data[$dataFieldName] = $this->extractValue($fieldName, $reflProperty->getValue($object), $object);
        }

        return $data;
    }
}
