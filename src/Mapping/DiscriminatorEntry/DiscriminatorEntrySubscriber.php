<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\DiscriminatorEntry;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\ObjectManager,
    Gedmo\Mapping\MappedEventSubscriber;

class DiscriminatorEntrySubscriber extends MappedEventSubscriber
{
    /**
     * Discriminator entry annotation class
     */
    const ENTRY_ANNOTATION = 'CmsDoctrine\Mapping\DiscriminatorEntry\Annotation\DiscriminatorEntry';

    /**
     * Discriminator entry annotation alias
     */
    const ENTRY_DOCUMENT_ANNOTATION_ALIAS = 'Doctrine\ODM\Mapping\DiscriminatorEntry';

    /**
     * Discriminator entry annotation alias
     */
    const ENTRY_ENTITY_ANNOTATION_ALIAS = 'Doctrine\ORM\Mapping\DiscriminatorEntry';

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        if (!class_exists(static::ENTRY_DOCUMENT_ANNOTATION_ALIAS)) {
            class_alias(static::ENTRY_ANNOTATION, static::ENTRY_DOCUMENT_ANNOTATION_ALIAS);
        }

        if (!class_exists(static::ENTRY_ENTITY_ANNOTATION_ALIAS)) {
            class_alias(static::ENTRY_ANNOTATION, static::ENTRY_ENTITY_ANNOTATION_ALIAS);
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
        $meta = $eventArgs->getClassMetadata();
        if (!$meta->isRootEntity() || $meta->isInheritanceTypeNone()
            || $meta->subClasses || $meta->isMappedSuperclass
        ) {
            return;
        }

        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $name = $meta->getName();

        $meta->subClasses = $this->getSubClasses($name, $om);
        $this->loadMetadataForObjectClass($om, $meta);
    }

    /**
     * @param string $class
     * @param ObjectManager $om
     * @return array
     */
    private function getSubClasses($class, ObjectManager $om)
    {
        $classes = [];
        foreach ($om->getConfiguration()->getMetadataDriverImpl()->getAllClassNames() as $className) {
            if (is_subclass_of($className, $class, true)) {
                $classes[] = $className;
            }
        }
        return $classes;
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
