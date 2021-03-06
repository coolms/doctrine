<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Uploadable;

use ArrayObject,
    Zend\EventManager\EventManagerAwareInterface,
    Zend\EventManager\EventManagerAwareTrait,
    Zend\EventManager\EventManagerInterface,
    Doctrine\Common\Collections\Collection,
    Doctrine\Common\EventArgs,
    Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\Persistence\ObjectManager,
    Gedmo\Mapping\Event\AdapterInterface,
    Gedmo\Mapping\MappedEventSubscriber,
    Gedmo\Uploadable\Events,
    Gedmo\Uploadable\Event\UploadablePostFileProcessEventArgs,
    Gedmo\Uploadable\Event\UploadablePreFileProcessEventArgs,
    Gedmo\Uploadable\FileInfo\FileInfoInterface,
    CmsCommon\Stdlib\ArrayUtils,
    CmsDoctrine\Mapping\Uploadable\FileInfo\FileInfoArray,
    CmsDoctrine\Mapping\Uploadable\PathGenerator\PathGeneratorInterface;

/**
 * Uploadable Manager
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class ManagerSubscriber extends MappedEventSubscriber implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     * @var string
     */
    protected $fileClass;

    /**
     * @var UploadableSubscriber
     */
    protected $uploadableSubscriber;

    /**
     * @var string
     */
    protected $eventIdentifier = 'UploadableManager';

    /**
     * @var array
     */
    private $uploadableSubscriberEvents = [];

    /**
     * __construct
     *
     * @param string $fileClass
     */
    public function __construct($fileClass)
    {
        parent::__construct();
        $this->setFileClass($fileClass);

        $this->uploadableSubscriberEvents = [
            Events::uploadablePreFileProcess,
            Events::uploadablePostFileProcess,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['loadClassMetadata', 'onFlush'];
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * @param EventArgs $args
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $em = $om->getEventManager();

        $em->removeEventListener(__FUNCTION__, $this);

        $this->initUploadableSubscriber($ea);
        $uow = $om->getUnitOfWork();
        $objects = array_merge($ea->getScheduledObjectInsertions($uow), $ea->getScheduledObjectUpdates($uow));

        $files = [];

        // check all scheduled insertions and updates
        foreach ($objects as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            if ($config = $this->getConfiguration($om, $meta->getName())) {
                $files = array_merge($files, $this->processFileUploads($ea, $config, $meta, $object));
            }
        }

        foreach ($files as $file) {
            $om->flush($file);
        }

        $em->addEventListener(__FUNCTION__, $this);
    }

    /**
     * @param AdapterInterface $ea
     * @param array $config
     * @param object $object
     * @throws \RuntimeException
     * @return array
     */
    protected function processFileUploads(AdapterInterface $ea, array &$config, $meta, $object)
    {
        $files = [];

        if (empty($config['uploadable'])) {
            return $files;
        }

        $uploads = $this->getPropertyValueFromObject($meta, $config['fileInfoField'], $object);
        if (!is_array($uploads) || !ArrayUtils::filterRecursive($uploads, null, true)) {
            // No uploaded files
            return $files;
        }

        if (ArrayUtils::hasStringKeys($uploads)) {
            $uploads = [$uploads];
        }

        $pathGenerator = null;
        if (!empty($config['pathGenerator'])) {
            $pathGenerator = $config['pathGenerator'];
        }

        if (empty($config['fileField']) || !$meta->hasAssociation($config['fileField'])) {
            $fileClass = $this->getFileClass();
        } else {
            $fileClass = $meta->getAssociationTargetClass($config['fileField']);
        }

        if (!$fileClass) {
            throw new \RuntimeException('Target file class cannot be found.');
        }

        $uploadableSubscriber = $this->getUploadableSubscriber();
        $fileInfoClass = $uploadableSubscriber->getDefaultFileInfoClass();
        foreach ($uploads as $fileInfoArray) {
            $file     = $this->createFile($fileClass, $fileInfoArray, $pathGenerator);
            $fileInfo = $this->createFileInfo($fileInfoClass, $fileInfoArray);
            $uploadableSubscriber->addEntityFileInfo($file, $fileInfo);
            $files[]  = $file;
        }

        $om = $ea->getObjectManager();

        if ($meta->hasAssociation($config['fileField'])) {
            $uow = $om->getUnitOfWork();
            $value = $meta->isCollectionValuedAssociation($config['fileField']) ? $files : $file;
            $this->updateField($object, $uow, $ea, $meta, $config['fileField'], $value);
            if ($meta->isCollectionValuedAssociation($config['fileField'])) {
                $uow->computeChangeSet($meta, $object);
            } else {
                $ea->recomputeSingleObjectChangeSet($uow, $meta, $object);
            }
        }

        $this->getEventManager()->trigger(__FUNCTION__, $this, $files);

        foreach ($files as $file) {
            $om->persist($file);
        }

        return $files;
    }

    /**
     * @param string $fileClass
     * @param array $fileInfoArray
     * @param string $pathGenerator
     * @return object
     */
    protected function createFile($fileClass, array $fileInfoArray, $pathGenerator)
    {
        $file = new $fileClass();
        $this->getEventManager()->trigger(__FUNCTION__, $this, compact('file', 'fileInfoArray', 'pathGenerator'));
        return $file;
    }

    /**
     * @param UploadablePreFileProcessEventArgs $args
     */
    public function uploadablePreFileProcess(UploadablePreFileProcessEventArgs $args)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, $args);
    }

    /**
     * @param UploadablePostFileProcessEventArgs $args
     */
    public function uploadablePostFileProcess(UploadablePostFileProcessEventArgs $args)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, $args);
    }

    /**
     * @param string $fileInfoClass
     * @param array $fileInfoArray
     * @return FileInfoInterface
     */
    protected function createFileInfo($fileInfoClass, array $fileInfoArray)
    {
        $fileInfoArray = new ArrayObject($fileInfoArray);
        $this->getEventManager()->trigger(__FUNCTION__, $this, compact('fileInfoClass', 'fileInfoArray'));
        return new $fileInfoClass($fileInfoArray->getArrayCopy());
    }

    /**
     * Returns value of the entity's property
     *
     * @param ClassMetadata $meta
     * @param string        $propertyName
     * @param object        $object
     * @return mixed
     */
    protected function getPropertyValueFromObject(ClassMetadata $meta, $propertyName, $object)
    {
        $refl = $meta->getReflectionClass();
        $prop = $refl->getProperty($propertyName);
        $prop->setAccessible(true);
        $value = $prop->getValue($object);
        return $value;
    }

    /**
     * @param object           $object
     * @param object           $uow
     * @param AdapterInterface $ea
     * @param ClassMetadata    $meta
     * @param String           $field
     * @param mixed            $value
     * @param bool             $notifyPropertyChanged
     */
    protected function updateField(
        $object,
        $uow,
        AdapterInterface $ea,
        ClassMetadata $meta,
        $field,
        $value,
        $notifyPropertyChanged = true
    ) {
        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);

        $ucField  = ucfirst($field);
        $prefixes = ['add', 'set', 'property'];

        foreach ($prefixes as $prefix) {
            $method = $prefix . $ucField;
            if (method_exists($object, $method)) {
                $object->$method($value);
                break;
            }

            if ($prefix === 'property') {
                $property->setValue($object, $value);
                break;
            }
        }

        if ($notifyPropertyChanged && $object instanceof NotifyPropertyChanged) {
            $uow->propertyChanged($object, $field, $oldValue, $value);
        }
    }

    /**
     * @param AdapterInterface $ea
     * @throws \RuntimeException
     */
    protected function initUploadableSubscriber(AdapterInterface $ea)
    {
        $em = $ea->getObjectManager()->getEventManager();

        if (null === $this->uploadableSubscriber) {
            foreach ($em->getListeners('loadClassMetadata') as $listener) {
                if ($listener instanceof UploadableSubscriber) {
                    $this->setUploadableSubscriber($listener);
                    break;
                }
            }

            if (!$this->uploadableSubscriber) {
                throw new RuntimeException("UploadableSubscriber can't be found");
            }
        }

        foreach ($this->uploadableSubscriberEvents as $event) {
            if (!$em->hasListeners($event) || !in_array($this, $em->getListeners($event), true)) {
                $em->addEventListener($event, $this);
            }
        }
    }

    /**
     * @param UploadableSubscriber $subscriber
     * @return self
     */
    public function setUploadableSubscriber(UploadableSubscriber $subscriber)
    {
        $subscriber->setDefaultFileInfoClass(FileInfoArray::class);
        $this->uploadableSubscriber = $subscriber;
        return $this;
    }

    /**
     * @return UploadableSubscriber
     */
    public function getUploadableSubscriber()
    {
        return $this->uploadableSubscriber;
    }

    /**
     * @param string $fileClass
     * @return self
     */
    public function setFileClass($fileClass)
    {
        $this->fileClass = $fileClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileClass()
    {
        return $this->fileClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
