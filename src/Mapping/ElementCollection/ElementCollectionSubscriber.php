<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\ElementCollection;

use Doctrine\Common\Collections\Collection,
    Doctrine\Common\EventArgs,
    Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\MappedEventSubscriber,
    CmsDoctrine\Mapping\ElementCollection\Mapping\Event\ElementCollectionAdapter;

/**
 * The ElementCollection listener handles the ElementCollection annotation
 * removed in Doctrine 2.5.
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class ElementCollectionSubscriber extends MappedEventSubscriber
{
    /**
     * ElementCollection annotation class
     */
    const ELEMENT_COLLECTION_ANNOTATION = 'CmsDoctrine\\Mapping\\ElementCollection\\Annotation\\ElementCollection';

    /**
     * ElementCollection annotation alias
     */
    const ELEMENT_COLLECTION_ODM_ANNOTATION_ALIAS = 'Doctrine\\ODM\\Mapping\\ElementCollection';

    /**
     * ElementCollection annotation alias
     */
    const ELEMENT_COLLECTION_ORM_ANNOTATION_ALIAS = 'Doctrine\\ORM\\Mapping\\ElementCollection';

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        if (!class_exists(static::ELEMENT_COLLECTION_ODM_ANNOTATION_ALIAS)) {
            class_alias(static::ELEMENT_COLLECTION_ANNOTATION, static::ELEMENT_COLLECTION_ODM_ANNOTATION_ALIAS);
        }

        if (!class_exists(static::ELEMENT_COLLECTION_ORM_ANNOTATION_ALIAS)) {
            class_alias(static::ELEMENT_COLLECTION_ANNOTATION, static::ELEMENT_COLLECTION_ORM_ANNOTATION_ALIAS);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return ['loadClassMetadata', 'postLoad'];
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
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postLoad(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();

        $meta = $om->getClassMetadata(get_class($object));

        if ($config = $this->getConfiguration($om, $meta->getName())) {
            foreach ($config as $options) {
                if (!empty($options['field']) && !empty($options['class'])) {
                    $value = $meta->getReflectionProperty($options['field'])->getValue($object);
                    if ($value === null || ($value instanceof Collection && $value->count() === 0)) { // let manual values
                        continue;
                    }

                    $this->updateField($object, $ea, $meta, $options['field'], $options['class']);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * Updates a field
     *
     * @param object                    $object
     * @param ElementCollectionAdapter  $ea
     * @param ClassMetadata             $meta
     * @param string                    $field
     * @param string                    $class
     */
    protected function updateField($object, $ea, $meta, $field, $class)
    {
        $property = $meta->getReflectionProperty($field);
        $collection = $ea->getElementCollection($meta, $field, $class);

        $setter = 'set' . ucfirst($field);
        if (method_exists($object, $setter)) {
            $object->$setter($collection);
        } else {
            $property->setValue($object, $collection);
        }
    }
}
