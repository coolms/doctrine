<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Hierarchy;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\Persistence\ObjectManager,
    Gedmo\Exception\InvalidMappingException,
    Gedmo\Tree\TreeListener;

class HierarchySubscriber extends TreeListener
{
    /**
     * @var string
     */
    protected $targetEntity = 'CmsCommon\\Mapping\\Hierarchy\\HierarchyInterface';

    /**
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $rc       = $metadata->getReflectionClass();

        if ($metadata->isMappedSuperclass || !$rc->isSubclassOf($this->targetEntity)) {
            return;
        }

        $name = $metadata->getName();
        if ($metadata->isRootEntity() && !$metadata->hasAssociation('parent')) {
            $metadata->mapManyToOne([
                'targetEntity'  =>  $name,
                'fieldName'     => 'parent',
                'inversedBy'    => 'children',
                'cascade'       => ['persist'],
            ]);
        }/* else {
            $assoc  = $metadata->getAssociationMappings()['parent'];
            $parent = $this->getParent($eventArgs, $metadata)->getName();
            if ($parent !== $assoc['targetEntity']) {
                unset($metadata->associationMappings['parent']);
                $assoc['targetEntity'] = $parent;
                $metadata->mapManyToOne($assoc);
            }
        }*/

        if (!$metadata->hasAssociation('children')) {
            $metadata->mapOneToMany([
                'targetEntity'  => $name,
                'fieldName'     => 'children',
                'mappedBy'      => 'parent',
                'orphanRemoval' => true,
                'cascade'       => ['persist','remove'],
                'fetch'         => 'EXTRA_LAZY',
            ]);
        } elseif ($metadata->subClasses) {
            $assoc = $metadata->getAssociationMappings()['children'];
            unset($metadata->associationMappings['children']);
            $assoc['targetEntity'] = $name;
            $metadata->mapOneToMany($assoc);
        }

        try {
        	parent::loadClassMetadata($eventArgs);
        } catch (InvalidMappingException $e) {
            if (stripos($e->getMessage(), 'cannot find tree type for class') !== 0) {
                throw $e;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(ObjectManager $objectManager, $class)
    {
        try {
            $config = parent::getConfiguration($objectManager, $class);
        } catch (InvalidMappingException $e) {
            if (stripos($e->getMessage(), 'cannot find tree type for class') === 0
                && is_subclass_of($class, $this->targetEntity, true)
            ) {
                return [];
            }
            throw $e;
        }

        return $config;
    }

    /**
     * @param EventArgs $eventArgs
     * @param ClassMetadata $metadata
     * @return ClassMetadata
     */
    protected function getParent(EventArgs $eventArgs, ClassMetadata $metadata)
    {
        if (!$metadata->isRootEntity()) {
            $parent = $metadata->getReflectionClass()->getParentClass();
            $parentMetadata = $eventArgs->getObjectManager()->getClassMetadata($parent->getName());

            if (!$parentMetadata->isMappedSuperclass) {
               return $parentMetadata;
            }

            return $this->getParent($eventArgs, $parentMetadata);
        }

        return $metadata;
    }
}
