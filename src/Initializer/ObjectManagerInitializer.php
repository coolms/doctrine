<?php
/**
 * CoolMS2 Doctrine Extentions Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Initializer;

use Zend\ServiceManager\AbstractPluginManager,
    Zend\ServiceManager\InitializerInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    DoctrineModule\Persistence\ObjectManagerAwareInterface;

class ObjectManagerInitializer implements InitializerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function initialize($instance, ServiceLocatorInterface $serviceLocator)
	{
        if ($instance instanceof ObjectManagerAwareInterface) {
            if ($serviceLocator instanceof AbstractPluginManager) {
            	$serviceLocator = $serviceLocator->getServiceLocator();
            }
            $objectManager = $serviceLocator->get('CmsDoctrine\\ObjectManager');
            $instance->setObjectManager($objectManager);
        }
	}
}
