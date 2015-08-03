<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation\Mapping\Event\Adapter;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Mapping\JoinColumn,
    Doctrine\ORM\Mapping\MappingException,
    Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    CmsDoctrine\Mapping\Annotation\RelationOverride,
    CmsDoctrine\Mapping\Relation\Mapping\Event\RelationAdapterInterface;

/**
 * Doctrine event adapter for ORM adapted for Relation behavior
 */
final class ORM extends BaseAdapterORM implements RelationAdapterInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws MappingException
     */
    public function mapRelations($meta, array $relationOverrides)
    {
        /* @var $relationOverride \CmsDoctrine\Mapping\Annotation\RelationOverride */
        foreach ($relationOverrides as $relationOverride) {
            if (!(($fieldName = $relationOverride->name) &&
                !$meta->isInheritedAssociation($fieldName))
            ) {
                continue;
            }

            $override = $this->relationToArray($relationOverride);

            // Check for JoinColumn|JoinColumns annotations
            if ($relationOverride->joinColumns) {
                $override['joinColumns'] = [];
                foreach ($associationOverride->joinColumns as $joinColumn) {
                    $override['joinColumns'][] = $this->joinColumnToArray($joinColumn);
                }
            }

            // Check for JoinTable annotations
            if ($joinTableAnnot = $relationOverride->joinTable) {
                $joinTable = [
                    'name' => $joinTableAnnot->name,
                    'schema' => $joinTableAnnot->schema
                ];

                foreach ($joinTableAnnot->joinColumns as $joinColumn) {
                    $joinTable['joinColumns'][] = $this->joinColumnToArray($joinColumn);
                }

                foreach ($joinTableAnnot->inverseJoinColumns as $joinColumn) {
                    $joinTable['inverseJoinColumns'][] = $this->joinColumnToArray($joinColumn);
                }

                $override['joinTable'] = $joinTable;
            }

            if ($meta->hasAssociation($fieldName)) {
                $mapping = $meta->getAssociationMapping($fieldName);

                if (isset($override['type']) &&
                    !$this->guardAssociationTypeOverride($mapping['type'], $override['type'])
                ) {
                    throw new MappingException(sprintf(
                        'Can\'t override mapping type of %s',
                        $meta->getName() . '::$' . $fieldName
                    ));
                }

                $override = array_replace($mapping, $override);
                unset($meta->associationMappings[$fieldName]);

            } elseif (!$meta->getReflectionClass()->isAbstract()) {
                $class = $meta->getReflectionClass();
                $prop = $class->getProperty($fieldName);
                $prop->setAccessible(true);
                $value = $prop->getValue($class->newInstanceWithoutConstructor());
                if (!$this->guardAssociationTypeMapping($value, $override['type'])) {
                    throw new MappingException(sprintf(
                        'Can\'t set association mapping on %s',
                        $meta->getName() . '::$' . $fieldName
                    ));
                }
            }

            if (!isset($override['mappedBy'])) { // owning side
                /* @var $objectManager \Doctrine\ORM\EntityManager */
                $objectManager  = $this->getObjectManager();
                /* @var $namingStrategy \CmsDoctrineORM\Mapping\DefaultNamingStrategy */
                $namingStrategy = $objectManager->getConfiguration()->getNamingStrategy();

                if ($override['type'] === ClassMetadata::MANY_TO_MANY || !empty($override['joinTable'])) {
                    if (empty($override['joinTable']['name'])) {
                        $override['joinTable']['name'] = $namingStrategy->joinTableName(
                            $meta->getName(),
                            $override['targetEntity'],
                            $override['fieldName']
                        );
                    }

                    if (!empty($override['joinTable']['joinColumns'])) {
                        foreach ($override['joinTable']['joinColumns'] as $key => $column) {
                            if (empty($column['name'])) {
                                $override['joinTable']['joinColumns'][$key]['name'] =
                                    $namingStrategy->joinKeyColumnName($meta->getName());
                            }
                        }
                    } else {
                        $override['joinTable']['joinColumns'] = [
                            [
                                'name' => $namingStrategy->joinKeyColumnName($meta->getName()),
                                'referencedColumnName' => $namingStrategy->referenceColumnName(),
                            ],
                        ];
                    }

                    if (!empty($override['joinTable']['inverseJoinColumns'])) {
                        foreach ($override['joinTable']['inverseJoinColumns'] as $key => $column) {
                            if (empty($column['name'])) {
                                $override['joinTable']['inverseJoinColumns'][$key]['name'] =
                                    $namingStrategy->joinKeyColumnName($override['targetEntity']);
                            }
                        }
                    } else {
                        $override['joinTable']['inverseJoinColumns'] = [
                            [
                                'name' => $namingStrategy->joinKeyColumnName($override['targetEntity']),
                                'referencedColumnName' => $namingStrategy->referenceColumnName(),
                            ],
                        ];
                    }
                } else {
                    if (!empty($override['joinColumn'])) {
                        if (empty($override['joinColumn']['name'])) {
                            $override['joinColumn']['name']
                                = $namingStrategy->joinColumnName($override['fieldName']);
                        }
                    } else {
                        $override['joinColumn'] = [
                            'name' => $namingStrategy->joinColumnName($override['fieldName']),
                            'referencedColumnName' => $namingStrategy->referenceColumnName(),
                        ];
                    }
                }
            }

            switch ($override['type']) {
                case ClassMetadata::ONE_TO_ONE:
                    $meta->mapOneToOne($override);
                    break;
                case ClassMetadata::ONE_TO_MANY:
                    $meta->mapOneToMany($override);
                    break;
                case ClassMetadata::MANY_TO_ONE:
                    $meta->mapManyToOne($override);
                    break;
                case ClassMetadata::MANY_TO_MANY:
                    $meta->mapManyToMany($override);
                    break;
            }
        }
    }

    /**
     * Parse the given RelationOverride as array
     *
     * @param RelationOverride $relation
     * @return array
     */
    private function relationToArray(RelationOverride $relation)
    {
        if ($relation->type && !is_numeric($relation->type)) {
            $relation->type = constant("\Doctrine\ORM\Mapping\ClassMetadata::{$relation->type}");
        }

        return array_filter([
            'type' => $relation->type,
            'targetEntity' => $relation->targetEntity,
            'fieldName' => $relation->name,
            'inversedBy' => $relation->inversedBy,
            'mappedBy' => $relation->mappedBy,
            'fetch' => $relation->fetch,
            'cascade' => $relation->cascade,
            'orphanRemoval' => $relation->orphanRemoval,
        ]);
    }

    /**
     * Parse the given JoinColumn as array
     *
     * @param JoinColumn $joinColumn
     * @return array
     */
    private function joinColumnToArray(JoinColumn $joinColumn)
    {
        return [
            'name' => $joinColumn->name,
            'unique' => $joinColumn->unique,
            'nullable' => $joinColumn->nullable,
            'onDelete' => $joinColumn->onDelete,
            'columnDefinition' => $joinColumn->columnDefinition,
            'referencedColumnName' => $joinColumn->referencedColumnName,
        ];
    }

    /**
     * @param number $oldType
     * @param number $newType
     * @return bool
     */
    private function guardAssociationTypeOverride($oldType, $newType)
    {
        switch ($oldType) {
            case ClassMetadata::ONE_TO_MANY:
            case ClassMetadata::MANY_TO_MANY:
                return ($newType === ClassMetadata::ONE_TO_MANY
                    || $newType === ClassMetadata::MANY_TO_MANY);
            case ClassMetadata::ONE_TO_ONE:
            case ClassMetadata::MANY_TO_ONE:
                return ($newType === ClassMetadata::ONE_TO_ONE
                    || $newType === ClassMetadata::MANY_TO_ONE);
        }
    }

    /**
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    private function guardAssociationTypeMapping($value, $type)
    {
        switch ($type) {
            case ClassMetadata::ONE_TO_MANY:
            case ClassMetadata::MANY_TO_MANY:
                return (is_array($value) || $value instanceof \Traversable);
            case ClassMetadata::ONE_TO_ONE:
            case ClassMetadata::MANY_TO_ONE:
                return (null === $value || is_scalar($value));
        }
    }
}
