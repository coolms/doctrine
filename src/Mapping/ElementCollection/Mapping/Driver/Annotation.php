<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * ElementCollection annotation class
     */
    const ANNOTATION = 'CmsDoctrine\Mapping\ElementCollection\Annotation\ElementCollection';

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                isset($meta->associationMappings[$property->name]['inherited'])
            ) {
                continue;
            }

            $elementCollection = $this->reader->getPropertyAnnotation($property, static::ANNOTATION);
            if (!empty($elementCollection->value)) {
                $config[] = [
                    'field' => $property->name,
                    'class' => $elementCollection->value,
                ];
            }
        }
    }
}
