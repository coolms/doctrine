<?php
/**
 * CoolMS2 Doctrine module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/CmsDoctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2014 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Translatable;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\Common\Persistence\Proxy,
    Gedmo\Translatable\TranslatableListener;

class TranslatableSubscriber extends TranslatableListener
{
    /**
     * @var string
     */
    protected $translationEntity = 'CmsDoctrineORM\\Mapping\\Translatable\\MappedSuperclass\\AbstractTranslation';

    /**
     * @var string
     */
    protected $translatableEntity = 'CmsDoctrine\\Mapping\\Translatable\\TranslatableInterface';

    /**
     * @var string
     */
    private $defaultTranslationClass;

    /**
     * @param EventArgs $eventArgs
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        if (null === $this->defaultTranslationClass) {
            $ea = $this->getEventAdapter($eventArgs);
            $this->defaultTranslationClass = $ea->getDefaultTranslationClass();
        }

        parent::loadClassMetadata($eventArgs);

        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->isMappedSuperclass) {
            return;
        }

        $name   = $metadata->getName();
        $rc     = $metadata->getReflectionClass();

        if ($rc->hasProperty('translations')
            && !$metadata->hasAssociation('translations')
            && $rc->isSubclassOf($this->translatableEntity)
        ) {
            $config = $this->getConfiguration($eventArgs->getObjectManager(), $name);
            if (isset($config['translationClass'])) {
                $metadata->mapOneToMany([
                    'targetEntity'  => $config['translationClass'],
                    'fieldName'     => 'translations',
                    'mappedBy'      => 'object',
                    'orphanRemoval' => true,
                    'cascade'       => ['persist','remove'],
                    'fetch'         => 'EXTRA_LAZY',
                ]);
            }
        } elseif ($rc->isSubclassOf($this->translationEntity)
            && $rc->hasProperty('object')
            && !$metadata->hasAssociation('object')
            && ($translatable = $this->getTranslatableEntity($name))
        ) {
            $om = $eventArgs->getObjectManager();
            $namingStrategy = $om->getConfiguration()->getNamingStrategy();

            $metadata->mapManyToOne([
                'targetEntity'  => $translatable,
                'fieldName'     => 'object',
                'inversedBy'    => 'translations',
                'joinColumn'    => [
                    'name'                  => $namingStrategy->joinColumnName('object'),
                    'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                    'onDelete'              => 'CASCADE',
                    'onUpdate'              => 'CASCADE',
                ],
            ]);
        }
    }

    /**
     * @param string $class
     * @return closure
     */
    private function getTranslatableEntity($class)
    {
        if (isset(self::$configurations[$this->name])
            && ($result = array_filter(self::$configurations[$this->name], function($config) use ($class) {
                return isset($config['translationClass']) && $config['translationClass'] === $class;
            }))
        ) {
            return array_keys($result)[0];
        }
    }

    /**
     * {@iheritDoc}
     */
    public function getConfiguration(ObjectManager $objectManager, $class)
    {
        $config = parent::getConfiguration($objectManager, $class);

        if (!empty($config['fields'])
            && empty($config['translationClass'])
            && !$this->isEntity($objectManager, $this->defaultTranslationClass)
        ) {
            return [];
        }

        return $config;
    }

    /**
     * @param ObjectManager $objectManager
     * @param string|object $class
     *
     * @return bool
     */
    public function isEntity(ObjectManager $objectManager, $class)
    {
        if (!$class) {
            return false;
        }
        if (is_object($class)) {
            $class = ($class instanceof Proxy)
                ? get_parent_class($class)
                : get_class($class);
        }

        return !$objectManager->getMetadataFactory()->isTransient($class);
    }
}
