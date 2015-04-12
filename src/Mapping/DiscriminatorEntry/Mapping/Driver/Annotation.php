<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\DiscriminatorEntry\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * Discriminator entry annotation class
     */
    const ENTRY_ANNOTATION = 'CmsDoctrine\Mapping\DiscriminatorEntry\Annotation\DiscriminatorEntry';

    /**
     * Our temporary map for calculations
     * 
     * @var array
     */
    private $map = [];

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        if (!$meta->subClasses) {
            return;
        }
        
        $classNames = $meta->subClasses;
        array_unshift($classNames, $meta->name);
        
        $map = $meta->discriminatorMap;
        foreach ($classNames as $className) {
            if(($entry = $this->getEntryName($className)) && !isset($map[$entry]) && !in_array($className, $map)) {
                $map[$entry] = $className;
            } elseif (null === $entry && in_array($className, $map)) {
                unset($map[$className], $meta->discriminatorMap[$className]);
            }
        }
        $meta->setDiscriminatorMap($map);
    }

    /**
     * @param string $class
     * @return bool
     */
    private function getEntryName($class)
    {
        $rc = new \ReflectionClass($class);
        if (!$rc->isAbstract()
            && ($annotation = $this->reader->getClassAnnotation($rc, static::ENTRY_ANNOTATION))
        ) {
            return $annotation->value;
        }
    }
}
