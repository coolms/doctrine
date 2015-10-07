<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine;

use Zend\Loader\ClassMapAutoloader,
    Zend\Loader\StandardAutoloader,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\InitProviderInterface,
    Zend\ModuleManager\ModuleManagerInterface;

class Module implements
    AutoloaderProviderInterface,
    InitProviderInterface
{
    /**
     * @param ModuleManagerInterface $moduleManager
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $moduleManager->loadModule('CmsCommon');
        $moduleManager->loadModule('DoctrineModule');

        spl_autoload_register(function($class) {
            if (strpos($class, 'Doctrine\\ORM\\Mapping') === 0 ||
                strpos($class, 'Doctrine\\ODM\\Mapping') === 0
            ) {
                $namespaces = ['Doctrine\\ORM\\Mapping', 'Doctrine\\ODM\\Mapping'];

                $alias = str_replace($namespaces, 'CmsDoctrine\\Mapping\\Annotation', $class);
                if (class_exists($alias)) {
                    return class_alias($alias, $class);
                }

                $alias = str_replace($namespaces, 'Gedmo\\Mapping\\Annotation', $class);
                if (class_exists($alias)) {
                    return class_alias($alias, $class);
                }
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return [
            ClassMapAutoloader::class => [
                __DIR__ . '/../autoload_classmap.php',
            ],
            StandardAutoloader::class => [
                'fallback_autoloader' => true,
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ],
            ],
        ];
    }
}
