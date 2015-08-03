<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable\Mapping\Driver;

use Gedmo\Timestampable\Mapping\Driver\Annotation as TimestampableAnnotation;

class Annotation extends TimestampableAnnotation
{
    /**
     * Changeable annotation class
     */
    const CHANGEABLE_ANNOTATION = 'CmsDoctrine\\Mapping\\Annotation\\ChangeableObject';

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
            $property = $class->getProperty(static::CHANGEABLE_PROPERTY);
            $changeable = $this->reader->getClassAnnotation($class, static::CHANGEABLE_ANNOTATION);
            if (!empty($changeable->field)) {
                if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                    $meta->isInheritedField($property->name) ||
                    $meta->isInheritedAssociation($property->name)
                ) {
                    $config['change'][] = [
                        'field' => $property->name,
                        'trackedField' => $changeable->field,
                        'value' => $changeable->value,
                    ];
                } else {
                    $timestampable = $this->reader->getPropertyAnnotation($property, static::TIMESTAMPABLE);
                    $timestampable->field = $changeable->field;
                    $timestampable->value = $changeable->value;
                }
            } else {
                $timestampable = $this->reader->getPropertyAnnotation($property, static::TIMESTAMPABLE);
                if (null === $timestampable->field) {
                    $timestampable->field = [];
                    $timestampable->value = null;
                }
            }
        }

        parent::readExtendedMetadata($meta, $config);
    }
}
