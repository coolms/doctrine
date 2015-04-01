<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable\Mapping\Driver;

use Gedmo\Timestampable\Mapping\Driver\Annotation as BaseAnnotation;

class Annotation extends BaseAnnotation
{
    /**
     * Changeable annotation class
     */
    const CHANGEABLE = 'CmsDoctrine\Mapping\Dateable\Annotation\Changeable';

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
        if ($class->hasProperty(self::CHANGEABLE_PROPERTY)) {
            $changeable = $this->reader->getClassAnnotation($class, self::CHANGEABLE);
            if (!empty($changeable->field)) {
                $property = $class->getProperty(self::CHANGEABLE_PROPERTY);
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
                    $timestampable = $this->reader->getPropertyAnnotation($property, self::TIMESTAMPABLE);
                    if (!$timestampable->field) {
                        $timestampable->field = $changeable->field;
                    }
                }
            }
        }

        parent::readExtendedMetadata($meta, $config);
    }
}
