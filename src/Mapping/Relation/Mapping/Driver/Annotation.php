<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\MappingException,
    Gedmo\Mapping\Driver\AbstractAnnotationDriver,
    CmsDoctrine\Mapping\Annotation\RelationOverride,
    CmsDoctrine\Mapping\Annotation\RelationOverrides;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * {@inheritDoc}
     *
     * @throws MappingException
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        /* @var $relationOverrides RelationOverrides */
        if (!($relationOverrides = $this->reader->getClassAnnotation($class, RelationOverrides::class))) {
            return;
        }

        $config['relationOverrides'] = [];

        /* @var $relationOverride RelationOverride */
        foreach ($relationOverrides->value as $relationOverride) {
            $fieldName = $relationOverride->name;

            if (!$class->hasProperty($fieldName)) {
                throw new MappingException(sprintf(
                    "Property %s doesn't exist",
                    $meta->getName() . '::$' . $fieldName
                ));
            }

            if ($meta->isMappedSuperclass && !$class->getProperty($fieldName)->isPrivate() ||
                $meta->isInheritedField($fieldName) ||
                $meta->hasField($fieldName))
            {
                continue;
            }

            $config['relationOverrides'][] = $relationOverride;
        }
    }
}
