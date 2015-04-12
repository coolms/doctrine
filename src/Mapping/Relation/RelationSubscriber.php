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
    Gedmo\Mapping\MappedEventSubscriber;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class RelationSubscriber extends MappedEventSubscriber
{
    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * __constructor
     *
     * @param string $objectType
     * @param array  $mapping
     */
    public function __construct($objectType, array $mapping)
    {
        parent::__construct();

        $this->objectType = $objectType;
        $this->mapping    = $mapping;
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
        if ($meta->isMappedSuperclass || empty($this->mapping) || empty($this->objectType)) {
            return;
        }

        /* @var $ea \Gedmo\Mapping\Event\AdapterInterface */
        $ea = $this->getEventAdapter($eventArgs);
        /* @var $om \Doctrine\Common\Persistence\ObjectManager */
        $om = $ea->getObjectManager();

        $this->loadMetadataForObjectClass($om, $meta);

        if ($meta->getReflectionClass()->isSubclassOf($this->objectType)) {
            $ea->mapAssociation($meta, $this->mapping);
        } else {
            $ea->remapAssociation($meta, $this->mapping, $this->objectType);
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
