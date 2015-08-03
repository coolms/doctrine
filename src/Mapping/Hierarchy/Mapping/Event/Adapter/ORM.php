<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Hierarchy\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    CmsDoctrine\Mapping\Hierarchy\Mapping\Event\TreeAdapterInterface;

/**
 * {@inheritDoc}
 */
class ORM extends BaseAdapterORM implements TreeAdapterInterface
{
    /**
     * @var string
     */
    protected $hierarchyClassName = 'CmsCommon\\Mapping\\Hierarchy\\HierarchyInterface';

    /**
     * {@inheritDoc}
     */
    public function mapHierarchy(ClassMetadata $meta)
    {
        if ($meta->isMappedSuperclass || !$meta->isRootEntity()) {
            return;
        }

        $rc = $meta->getReflectionClass();
        if ($rc->hasProperty('parent') && !$meta->hasAssociation('parent')) {
            $meta->mapManyToOne([
                'targetEntity'  =>  $meta->getName(),
                'fieldName'     => 'parent',
                'inversedBy'    => 'children',
                'cascade'       => ['persist'],
            ]);
        }

        if ($rc->hasProperty('children') && !$meta->hasAssociation('children')) {
            $meta->mapOneToMany([
                'targetEntity'  => $meta->getName(),
                'fieldName'     => 'children',
                'mappedBy'      => 'parent',
                'cascade'       => ['persist','remove'],
                'fetch'         => 'EXTRA_LAZY',
            ]);
        }
    }

    /**
     * @return string
     */
    public function getHierarchyClassName()
    {
        return $this->hierarchyClassName;
    }
}
