<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Blameable\Mapping\Driver;

use Gedmo\Blameable\Mapping\Driver\Annotation as BlameableAnnotation,
    CmsDoctrine\Mapping\Dateable\Mapping\Driver\Annotation as TimestampableAnnotation;

class Annotation extends BlameableAnnotation
{
    /**
     * changedBy property
     */
    const CHANGEDBY_PROPERTY = 'changedBy';

    /**
     * createdBy property
     */
    const CREATEDBY_PROPERTY = 'createdBy';

    /**
     * updatedBy property
     */
    const UPDATEDBY_PROPERTY = 'updatedBy';

    /**
     * @var array
     */
    protected $blames = [];

    /**
     * __construct
     */
    public function __construct()
    {
        $this->blames = [
            static::CHANGEDBY_PROPERTY,
            static::CREATEDBY_PROPERTY,
            static::UPDATEDBY_PROPERTY,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        foreach ($this->blames as $blame) {
            if (!$class->hasProperty($blame)) {
                continue;
            }

            $property = $class->getProperty($blame);
            if (!$meta->hasField($blame) && !$meta->hasAssociation($blame)) {
                $config['blames'][] = $blame;
                $rootObject = $property->getDeclaringClass()->getName();
                if ($rootObject === $meta->getName()) {
                    $meta->mapField([
                        'fieldName' => $blame,
                        'type' => 'string',
                        'nullable' => $blame === static::CREATEDBY_PROPERTY ? false : true,
                    ]);
                } else {
                    $meta->mapField([
                        'fieldName' => $blame,
                        'inherited' => $rootObject,
                    ]);
                }
            }

            $changeable = $this->reader->getClassAnnotation($class, TimestampableAnnotation::CHANGEABLE_ANNOTATION);
            if (!empty($changeable->field)) {
                if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                    $meta->isInheritedField($blame) ||
                    $meta->isInheritedAssociation($blame)
                ) {
                    $config['change'][] = [
                        'field' => $blame,
                        'trackedField' => $changeable->field,
                        'value' => $changeable->value,
                    ];
                } else {
                    $blameable = $this->reader->getPropertyAnnotation($property, static::BLAMEABLE);
                    $blameable->field = $changeable->field;
                    $blameable->value = $changeable->value;
                }
            } else {
                $blameable = $this->reader->getPropertyAnnotation($property, static::BLAMEABLE);
                if (null === $blameable->field) {
                    $blameable->field = [];
                    $blameable->value = null;
                }
            }
        }

        parent::readExtendedMetadata($meta, $config);
    }
}
