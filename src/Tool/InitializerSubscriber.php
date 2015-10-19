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

use Zend\ServiceManager\InitializerInterface,
    Zend\ServiceManager\ServiceLocatorAwareTrait,
    Zend\ServiceManager\ServiceLocatorInterface,
    Doctrine\Common\EventArgs,
    Doctrine\Common\EventSubscriber;

class InitializerSubscriber implements EventSubscriber
{
    use ServiceLocatorAwareTrait;

    /**
     * @var InitializerInterface
     */
    protected $initializer;

    /**
     * __construct
     *
     * @param InitializerInterface $initializer
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(InitializerInterface $initializer, ServiceLocatorInterface $serviceLocator)
    {
        $this->initializer = $initializer;
        $this->setServiceLocator($serviceLocator);
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * @param EventArgs $eventArgs
     */
    public function postLoad(EventArgs $eventArgs)
    {
        $this->initialize($eventArgs->getObject());
    }

    /**
     * @param object $object
     */
    public function initialize($object)
    {
        $this->initializer->initialize($object, $this->getServiceLocator());
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
