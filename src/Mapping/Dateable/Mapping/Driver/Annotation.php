<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable\Mapping\Driver;

use Gedmo\Timestampable\Mapping\Driver\Annotation as BaseAnnotation,
    CmsDoctrine\Mapping\Dateable\TimestampableSubscriber;

class Annotation extends BaseAnnotation
{
    /**
     * Changeable class annotation
     */
    const CHANGEABLE = TimestampableSubscriber::CHANGEABLE_ANNOTATION;

    /**
     * Changeable property
     */
    const CHANGEABLE_PROPERTY = 'changedAt';

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        if ($class->hasProperty(static::CHANGEABLE_PROPERTY)) {
            $changeable = $this->reader->getClassAnnotation($class, static::CHANGEABLE);
            if (!empty($changeable->field)) {
                $property = $class->getProperty(static::CHANGEABLE_PROPERTY);
                if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                    $meta->isInheritedField($property->name) ||
                    isset($meta->associationMappings[$property->name]['inherited'])
                ) {
                    $config['change'][] = [
                        'field' => $property->name,
                        'trackedField' => $changeable->field,
                        'value' => $changeable->value,
                    ];
                } else {
                    $timestampable = $this->reader->getPropertyAnnotation($property, static::TIMESTAMPABLE);
                    if (!$timestampable->field) {
                        $timestampable->field = $changeable->field;
                    }
                }
            }
        }

        parent::readExtendedMetadata($meta, $config);
    }
}
