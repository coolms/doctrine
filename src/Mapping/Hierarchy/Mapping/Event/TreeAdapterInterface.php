<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Hierarchy\Mapping\Event;

use Gedmo\Tree\Mapping\Event\TreeAdapter,
    Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * {@inheritDoc}
 */
interface TreeAdapterInterface extends TreeAdapter
{
    /**
     * @param ClassMetadata $meta
     */
    public function mapHierarchy(ClassMetadata $meta);

    /**
     * @return string
     */
    public function getHierarchyClassName();
}
