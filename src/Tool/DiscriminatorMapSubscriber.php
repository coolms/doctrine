<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Tool;

use Doctrine\Common\EventArgs,
    Doctrine\Common\EventSubscriber;

class DiscriminatorMapSubscriber implements EventSubscriber
{
    /**
     * @var array
     */
    protected $discriminatorMaps = [];

    /**
     * __construct
     *
     * @param array $discriminatorMaps
     */
    public function __construct(array $discriminatorMaps)
    {
        $this->discriminatorMaps = $discriminatorMaps;
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
        if ($meta->isInheritanceTypeNone()) {
            return;
        }

        $className = $meta->getName();
        if (!empty($this->discriminatorMaps[$className])) {
            foreach ($this->discriminatorMaps[$className] as $entry => $className) {
                if (in_array($className, $meta->discriminatorMap)) {
                    $meta->discriminatorMap = array_diff($meta->discriminatorMap, [$className]);
                }
                $meta->discriminatorMap[$entry] = $className;
                if (!in_array($className, $meta->subClasses) && $className !== $meta->name) {
                    $meta->subClasses[] = $className;
                }
            }
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
