<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Hierarchy\Mapping\Driver;

use Gedmo\Mapping\Annotation\Tree,
    Gedmo\Tree\Mapping\Driver\Annotation as AnnotationDriver;

/**
 * {@inheritDoc}
 */
class Annotation extends AnnotationDriver
{
    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        if ($annot = $this->reader->getClassAnnotation($class, static::TREE)) {
            return parent::readExtendedMetadata($meta, $config);
        } elseif ($annot = $this->getTreeAnnotation($class)) {
            $config['strategy'] = $annot->type;
            $config['activate_locking'] = $annot->activateLocking;
            $config['locking_timeout'] = (int) $annot->lockingTimeout;

            if ($config['locking_timeout'] < 1) {
                throw new InvalidMappingException("Tree Locking Timeout must be at least of 1 second.");
            }

            return parent::readExtendedMetadata($meta, $config);
        }

        return [];
    }

    /**
     * @param \ReflectionClass $meta
     * @return Tree
     */
    private function getTreeAnnotation($class)
    {
        if (!$class) {
            return;
        }

        if (!$annot = $this->reader->getClassAnnotation($class, static::TREE)) {
            return $this->getTreeAnnotation($class->getParentClass());
        }

        return $annot;
    }
}
