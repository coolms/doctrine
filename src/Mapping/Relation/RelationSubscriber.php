<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation;

use Doctrine\Common\EventArgs,
    Doctrine\Common\EventManager,
    Doctrine\ORM\Tools\ResolveTargetEntityListener,
    Gedmo\Mapping\MappedEventSubscriber;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class RelationSubscriber extends MappedEventSubscriber
{
    /**
     * RelationOverrides annotation class
     */
    const RELATION_OVERRIDES_ANNOTATION = 'CmsDoctrine\\Mapping\\Relation\\Annotation\\RelationOverrides';

    /**
     * RelationOverrides annotation alias
     */
    const RELATION_OVERRIDES_ODM_ANNOTATION_ALIAS = 'Doctrine\\ODM\\Mapping\\RelationOverrides';

    /**
     * RelationOverrides annotation alias
     */
    const RELATION_OVERRIDES_ORM_ANNOTATION_ALIAS = 'Doctrine\\ORM\\Mapping\\RelationOverrides';

    /**
     * RelationOverride annotation class
     */
    const RELATION_OVERRIDE_ANNOTATION = 'CmsDoctrine\\Mapping\\Relation\\Annotation\\RelationOverride';

    /**
     * RelationOverride annotation alias
     */
    const RELATION_OVERRIDE_ODM_ANNOTATION_ALIAS = 'Doctrine\\ODM\\Mapping\\RelationOverride';

    /**
     * RelationOverride annotation alias
     */
    const RELATION_OVERRIDE_ORM_ANNOTATION_ALIAS = 'Doctrine\\ORM\\Mapping\\RelationOverride';

    /**
     * __constructor
     */
    public function __construct()
    {
        parent::__construct();

        if (!class_exists(static::RELATION_OVERRIDES_ODM_ANNOTATION_ALIAS)) {
            class_alias(static::RELATION_OVERRIDES_ANNOTATION, static::RELATION_OVERRIDES_ODM_ANNOTATION_ALIAS);
        }

        if (!class_exists(static::RELATION_OVERRIDES_ORM_ANNOTATION_ALIAS)) {
            class_alias(static::RELATION_OVERRIDES_ANNOTATION, static::RELATION_OVERRIDES_ORM_ANNOTATION_ALIAS);
        }

        if (!class_exists(static::RELATION_OVERRIDE_ODM_ANNOTATION_ALIAS)) {
            class_alias(static::RELATION_OVERRIDE_ANNOTATION, static::RELATION_OVERRIDE_ODM_ANNOTATION_ALIAS);
        }

        if (!class_exists(static::RELATION_OVERRIDE_ORM_ANNOTATION_ALIAS)) {
            class_alias(static::RELATION_OVERRIDE_ANNOTATION, static::RELATION_OVERRIDE_ORM_ANNOTATION_ALIAS);
        }
    }

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
        /* @var $ea \Gedmo\Mapping\Event\AdapterInterface */
        $ea = $this->getEventAdapter($eventArgs);
        /* @var $om \Doctrine\Common\Persistence\ObjectManager */
        $om = $ea->getObjectManager();
        /* @var $meta \Doctrine\Common\Persistence\Mapping\ClassMetadata */
        $meta = $eventArgs->getClassMetadata();

        $this->loadMetadataForObjectClass($om, $meta);

        $name = $meta->getName();
        if (!empty(self::$configurations[$this->name][$name]['relationOverrides'])) {
            $ea->mapRelations($meta, self::$configurations[$this->name][$name]['relationOverrides']);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
