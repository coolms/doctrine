<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Translatable\Mapping\Driver;

use Gedmo\Translatable\Mapping\Driver\Annotation as AnnotationDriver;

/**
 * {@inheritDoc}
 */
class Annotation extends AnnotationDriver
{
    /**
     * @var string
     */
    protected $defaultTransaltionClass = 'CmsDoctrine\\Mapping\\Translatable\\TranslationInterface';

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        // class annotations
        if ($annot = $this->reader->getClassAnnotation($class, self::ENTITY_CLASS)) {
            if (!$annot->class) {
                $annot->class = $this->defaultTransaltionClass;
            }
        } elseif (empty($config['translationClass'])) {
            return;
        }

        return parent::readExtendedMetadata($meta, $config);
    }
}
