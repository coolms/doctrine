<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Embedded\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\MappingException,
    Gedmo\Mapping\Driver\AbstractAnnotationDriver,
    CmsDoctrine\Mapping\Annotation\EmbeddedOverride,
    CmsDoctrine\Mapping\Annotation\EmbeddedOverrides;

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
        /* @var $embeddedOverrides EmbeddedOverrides */
        if (!($embeddedOverrides = $this->reader->getClassAnnotation($class, EmbeddedOverrides::class))) {
            return;
        }

        $config['embeddedOverrides'] = [];

        /* @var $embeddedOverride EmbeddedOverride */
        foreach ($embeddedOverrides->value as $embeddedOverride) {
            $fieldName = $embeddedOverride->name;

            if (false === strpos($fieldName, '.')) {
                continue;
            }

            list($fieldName, ) = explode('.', $fieldName);

            if (!$class->hasProperty($fieldName)) {
                throw new MappingException(sprintf(
                    "Property %s doesn't exist",
                    $meta->getName() . '::$' . $fieldName
                ));
            }

            if ($meta->isMappedSuperclass && !$class->getProperty($fieldName)->isPrivate() ||
                $meta->isInheritedField($embeddedOverride->name) ||
                !isset($meta->embeddedClasses[$fieldName])
            ) {
                continue;
            }

            $config['embeddedOverrides'][] = $embeddedOverride;
        }
    }
}
