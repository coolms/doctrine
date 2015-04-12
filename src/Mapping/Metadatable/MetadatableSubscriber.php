<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Metadatable;

use Doctrine\Common\EventArgs,
    Doctrine\Common\EventSubscriber;

class MetadatableSubscriber implements EventSubscriber
{
    protected $sourceEntity = 'CmsDoctrine\\Mapping\\Metadatable\\MetadataInterface';
    protected $targetEntity = 'CmsDoctrine\\Mapping\\Metadatable\\MetadatableInterface';

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->isMappedSuperclass) {
        	return;
        }

        $mappings = $metadata->getAssociationMappings();
        $name = $metadata->getName();

        if (!isset($mappings['metadata']) && in_array($this->targetEntity, class_implements($name))) {
            $reflClass = $metadata->getReflectionClass();
            $namespace = $reflClass->getNamespaceName();
            $classShortName = $reflClass->getShortName();

            $metadata->mapOneToMany([
                'targetEntity'  => "$namespace\\{$classShortName}Metadata",
                'fieldName'     => 'metadata',
                'mappedBy'      => 'object',
                'orphanRemoval' => true,
                'cascade'       => ['persist','remove'],
                'fetch'         => 'EXTRA_LAZY',
            ]);

        } elseif (!isset($mappings['object']) && is_subclass_of($name, $this->sourceEntity)) {

            $namingStrategy = $eventArgs
                ->getEntityManager()
                ->getConfiguration()
                ->getNamingStrategy();

            $metadata->mapManyToOne([
                'targetEntity'  => str_replace('Metadata', '', $name),
                'fieldName'     => 'object',
                'inversedBy'    => 'metadata',
                'joinColumn'    => [
                    'name'                  => 'object_id',
                    'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                    'onDelete'  => 'CASCADE',
                    'onUpdate'  => 'CASCADE',
                ],
            ]);
        }
    }
}
