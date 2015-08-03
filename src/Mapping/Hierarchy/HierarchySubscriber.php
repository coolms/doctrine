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

/**
 * The hierarchy subscriber handles the synchronization of
 * tree nodes. Can implement different
 * strategies on handling the tree.
 */
class HierarchySubscriber extends TreeListener
{
    /**
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $meta = $eventArgs->getClassMetadata();
        $ea = $this->getEventAdapter($eventArgs);

        if (is_subclass_of($meta->getName(), $ea->getHierarchyClassName(), true)) {
            $ea->mapHierarchy($meta);
        }

        parent::loadClassMetadata($eventArgs);
    }

    /**
     * {@inheritedDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
