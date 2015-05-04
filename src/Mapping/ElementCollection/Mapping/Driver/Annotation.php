<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver,
    CmsDoctrine\Mapping\ElementCollection\ElementCollectionSubscriber;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        foreach ($class->getProperties() as $property) {
            if ($meta->isMappedSuperclass && !$property->isPrivate() ||
                $meta->isInheritedField($property->name) ||
                $meta->isInheritedAssociation($property->name) ||
                $property->getDeclaringClass()->getName() !== $meta->getName()
            ) {
                continue;
            }

            $elementCollection = $this->reader->getPropertyAnnotation(
                $property,
                ElementCollectionSubscriber::ELEMENT_COLLECTION_ANNOTATION
            );

            if (!empty($elementCollection->value)) {
                $config[] = [
                    'field' => $property->name,
                    'class' => $elementCollection->value,
                ];
            }
        }
    }
}
