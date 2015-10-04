<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Embedded\Mapping\Event\Adapter;

use Doctrine\ORM\Mapping\Column,
    Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    CmsDoctrine\Mapping\Annotation\EmbeddedOverride,
    CmsDoctrine\Mapping\Embedded\Mapping\Event\EmbeddedAdapterInterface;

/**
 * Doctrine event adapter for ORM adapted for Relation behavior
 */
final class ORM extends BaseAdapterORM implements EmbeddedAdapterInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws MappingException
     */
    public function mapFields($meta, array $fieldOverrides)
    {
        /* @var $fieldOverride EmbeddedOverride */
        foreach ($fieldOverrides as $fieldOverride) {
            list($embedded, $property) = explode('.', $fieldOverride->name);

            if (!$meta->hasField($fieldOverride->name) || !isset($meta->embeddedClasses[$embedded])) {
                continue;
            }

            $fieldMapping = $meta->getFieldMapping($fieldOverride->name);
            $fieldMapping = array_merge($fieldMapping, $this->columnToArray($fieldOverride->column));

            $meta->setAttributeOverride($fieldOverride->name, $fieldMapping);
        }
    }

    /**
     * Parse the given Column as array
     *
     * @param Column $column
     * @return array
     */
    private function columnToArray(Column $column)
    {
        return array_filter([
            'columnName' => $column->name,
            'length' => $column->length,
            'nullable' => $column->nullable,
            'unique' => $column->unique,
            'options' => $column->options,
            'precision' => $column->precision,
            'scale' => $column->scale,
            'columnDefinition' => $column->columnDefinition,
        ]);
    }
}
