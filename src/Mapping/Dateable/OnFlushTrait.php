<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Dateable;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
trait OnFlushTrait
{
    /**
     * Looks for Timestampable objects being updated
     * to update modification date
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        // check all scheduled updates
        $all = array_merge($ea->getScheduledObjectInsertions($uow), $ea->getScheduledObjectUpdates($uow));
        foreach ($all as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            if (!$config = $this->getConfiguration($om, $meta->name)) {
                continue;
            }
            $changeSet = $ea->getObjectChangeSet($uow, $object);
            $needChanges = false;

            if ($uow->isScheduledForInsert($object) && isset($config['create'])) {
                foreach ($config['create'] as $field) {
                    list(, $new) = $changeSet[$field];
                    if ($new === null) { // let manual values
                        $needChanges = true;
                        $this->updateField($object, $ea, $meta, $field);
                    }
                }
            }

            if (isset($config['update'])) {
                foreach ($config['update'] as $field) {
                    $isInsertAndNull = $uow->isScheduledForInsert($object) && $changeSet[$field][1] === null;
                    if (!isset($changeSet[$field]) || $isInsertAndNull) { // let manual values
                        $needChanges = true;
                        $this->updateField($object, $ea, $meta, $field);
                    }
                }
            }

            if (!$uow->isScheduledForInsert($object) && isset($config['change'])) {
                foreach ($config['change'] as $options) {
                    if (isset($changeSet[$options['field']])) {
                        continue; // value was set manually
                    }

                    if (!is_array($options['trackedField'])) {
                        $singleField = true;
                        $trackedFields = array($options['trackedField']);
                    } else {
                        $singleField = false;
                        $trackedFields = $options['trackedField'];
                    }

                    foreach ($trackedFields as $tracked) {
                        $trackedChild = null;
                        $parts = explode('.', $tracked);
                        if (isset($parts[1])) {
                            if (isset($changeSet[$tracked])) {
                                $changes = $changeSet[$tracked];
                                if ($changes[0] != $changes[1]) {
                                    $needChanges = true;
                                    $this->updateField($object, $ea, $meta, $options['field']);
                                    continue;
                                }
                            }

                            $tracked = $parts[0];
                            $trackedChild = $parts[1];
                        }

                        if (isset($changeSet[$tracked])) {
                            $changes = $changeSet[$tracked];
                            if (isset($trackedChild)) {
                                $changingObject = $changes[1];
                                if (!is_object($changingObject)) {
                                    throw new UnexpectedValueException(
                                        "Field - [{$field}] is expected to be object in class - {$meta->name}"
                                    );
                                }
                                $objectMeta = $om->getClassMetadata(get_class($changingObject));
                                $om->initializeObject($changingObject);
                                $value = $objectMeta->getReflectionProperty($trackedChild)->getValue($changingObject);
                            } else {
                                $value = $changes[1];
                            }

                            if (($singleField && in_array($value, (array) $options['value'])) || $options['value'] === null) {
                                $needChanges = true;
                                $this->updateField($object, $ea, $meta, $options['field']);
                            }
                        }
                    }
                }
            }

            if ($needChanges) {
                $ea->recomputeSingleObjectChangeSet($uow, $meta, $object);
            }
        }
    }

    /**
     * Get an event adapter to handle event specific methods
     *
     * @param EventArgs $args
     * @throws \Gedmo\Exception\InvalidArgumentException - if event is not recognized
     * @return \Gedmo\Mapping\Event\AdapterInterface
     */
    abstract protected function getEventAdapter(EventArgs $args);

    /**
     * Get the configuration for specific object class
     * if cache driver is present it scans it also
     *
     * @param ObjectManager $objectManager
     * @param string        $class
     * @return array
     */
    abstract public function getConfiguration(ObjectManager $objectManager, $class);

    /**
     * Updates a field
     *
     * @param object               $object
     * @param TimestampableAdapter $ea
     * @param ClassMetadata        $meta
     * @param string               $field
     */
    abstract protected function updateField($object, $ea, $meta, $field);
}
