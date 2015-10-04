<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Relation;

use Doctrine\Common\EventArgs,
    Gedmo\Mapping\MappedEventSubscriber;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class OverrideSubscriber extends MappedEventSubscriber
{
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
