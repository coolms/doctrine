<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation\Mapping\Event\Adapter;

use Doctrine\ORM\Mapping\ClassMetadata,
    Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    CmsDoctrine\Mapping\Relation\Mapping\Event\RelationAdapterInterface;

/**
 * Doctrine event adapter for ORM adapted for Relation behavior
 */
final class ORM extends BaseAdapterORM implements RelationAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function mapAssociation($classMetadata, $mapping)
    {
        if (empty($mapping['fieldName'])
            || empty($mapping['targetEntity'])
            || empty($mapping['type'])
            || $classMetadata->hasAssociation($mapping['fieldName'])
        ) {
            return;
        }

        $this->map($classMetadata, $mapping);
    }

    /**
     * {@inheritDoc}
     */
    public function remapAssociation($classMetadata, $mapping, $objectType)
    {
        if (empty($mapping['fieldName']) || empty($mapping['targetEntity'])) {
            return;
        }

        $className      = $classMetadata->getName();
        $targetEntity   = $mapping['targetEntity'];
        if (!$className instanceof $targetEntity) {
            return;
        }

        /* @var $objectManager \Doctrine\Common\Persistence\ObjectManager */
        $objectManager  = $this->getObjectManager();
        $mappings       = $classMetadata->getAssociationMappings();
        foreach ($mappings as $name => $assoc) {
            $objectClass = $assoc['targetEntity'];
            if (!$objectClass instanceof $objectType) {
                continue;
            }

            if (!empty($assoc['mappedBy']) && $assoc['mappedBy'] === $mapping['fieldName']) {
                $mapping['type'] = $this->inverseType($assoc['type']);
                $mapping['inversedBy'] = $name;
                $this->map($objectManager->getClassMetadata($objectClass), $mapping);
                break;
            } elseif (!empty($assoc['inversedBy']) && $assoc['inversedBy'] === $mapping['fieldName']) {
                $mapping['type'] = $this->inverseType($assoc['type']);
                $mapping['mappedBy'] = $name;
                $this->map($objectManager->getClassMetadata($objectClass), $mapping);
                break;
            }
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param array $mapping
     *
     * @return void
     */
    private function map($classMetadata, $mapping)
    {
        if (!isset($mapping['mappedBy'])) { // owning side
            /* @var $objectManager \Doctrine\Common\Persistence\ObjectManager */
            $objectManager  = $this->getObjectManager();
            /* @var $namingStrategy \CmsDoctrineORM\Mapping\DefaultNamingStrategy */
            $namingStrategy = $objectManager->getConfiguration()->getNamingStrategy();
            if ($mapping['type'] === ClassMetadata::MANY_TO_MANY) {
                $className = $classMetadata->getName();
                $mapping['joinTable'] = [
                    'name' => $namingStrategy->joinTableName($className, $mapping['targetEntity'], $mapping['fieldName']),
                    'joinColumns' => [
                        [
                            'name'                  => $namingStrategy->joinKeyColumnName($className),
                            'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                        ],
                    ],
                    'inverseJoinColumns'=> [
                        [
                            'name'                  => $namingStrategy->joinKeyColumnName($mapping['targetEntity']),
                            'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                        ],
                    ],
                ];
            } else {
                $mapping['joinColumn'] = [
                    'name'                  => $namingStrategy->joinColumnName($mapping['fieldName']),
                    'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                ];
            }
        }

        if ($classMetadata->hasAssociation($mapping['fieldName'])) {
            $mapping = array_replace_recursive($classMetadata->getAssociationMapping($mapping['fieldName']), $mapping);
            unset($classMetadata->associationMappings[$mapping['fieldName']]);
        }

        switch ($mapping['type']) {
            case ClassMetadata::ONE_TO_ONE:
                $classMetadata->mapOneToOne($mapping);
                break;
            case ClassMetadata::ONE_TO_MANY:
                $classMetadata->mapOneToMany($mapping);
                break;
            case ClassMetadata::MANY_TO_ONE:
                $classMetadata->mapManyToOne($mapping);
                break;
            case ClassMetadata::MANY_TO_MANY:
                $classMetadata->mapManyToMany($mapping);
                break;
        }
    }

    /**
     * @param int $assocType
     * @return int
     */
    private function inverseType($assocType)
    {
        if ($assocType === ClassMetadata::ONE_TO_MANY) {
            return ClassMetadata::MANY_TO_ONE;
        }
        if ($assocType === ClassMetadata::MANY_TO_ONE) {
            return ClassMetadata::ONE_TO_MANY;
        }

        return $assocType;
    }
}
