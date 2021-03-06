<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Translatable\Mapping\Event\Adapter;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM,
    Gedmo\Tool\Wrapper\AbstractWrapper,
    Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation,
    Gedmo\Translatable\Mapping\Event\Adapter\ORM as EventAdapter,
    CmsDoctrine\Mapping\Translatable\Mapping\Event\TranslatableAdapterInterface,
    CmsDoctrine\Mapping\Translatable\TranslatableInterface,
    CmsDoctrine\Mapping\Translatable\TranslationInterface;

/**
 * Doctrine event adapter for ORM adapted for Translatable behavior
 */
class ORM extends BaseAdapterORM implements TranslatableAdapterInterface
{
    /**
     * @var string
     */
    protected $translatableClass = TranslatableInterface::class;

    /**
     * @var string
     */
    protected $defaultTranslationClass = TranslationInterface::class;

    /**
     * @var string
     */
    private $personalTranslation = AbstractPersonalTranslation::class;

    /**
     * @var EventAdapter
     */
    private $ea;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->ea = new EventAdapter();
    }

    /**
     * {@inheritDoc}
     */
    public function mapTranslatable(ClassMetadata $meta, $translationClassName)
    {
        $rc = $meta->getReflectionClass();
        if (!$rc->hasProperty('translations') ||
            $meta->hasAssociation('translations') ||
            !$rc->isSubclassOf($this->translatableClass)
        ) {
            return;
        }

        if (!$meta->hasAssociation('translations')) {
            $meta->mapOneToMany([
                'targetEntity'  => $translationClassName,
                'fieldName'     => 'translations',
                'mappedBy'      => 'object',
                'orphanRemoval' => true,
                'cascade'       => ['persist','remove'],
                'fetch'         => 'EXTRA_LAZY',
                'inherited'     => $this->getRootObjectClass($meta),
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function mapTranslation(ClassMetadata $meta, $translatableClassName)
    {
        $rc = $meta->getReflectionClass();
        if (!$rc->hasProperty('object') ||
            $meta->hasAssociation('object') ||
            !$rc->isSubclassOf($this->personalTranslation)
        ) {
            return;
        }

        $namingStrategy = $this->getObjectManager()->getConfiguration()->getNamingStrategy();
        $meta->mapManyToOne([
            'targetEntity'  => $translatableClassName,
            'fieldName'     => 'object',
            'inversedBy'    => 'translations',
            'isOwningSide'  => true,
            'joinColumns'   => [[
                'name'                  => $namingStrategy->joinColumnName('object'),
                'referencedColumnName'  => $namingStrategy->referenceColumnName(),
                'onDelete'              => 'CASCADE',
                'onUpdate'              => 'CASCADE',
            ]],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setEventArgs(EventArgs $args)
    {
        parent::setEventArgs($args);
        $this->ea->setEventArgs($args);
    }

    /**
     * {@inheritDoc}
     */
    public function usesPersonalTranslation($translationClassName)
    {
        return $this->ea->usesPersonalTranslation($translationClassName);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultTranslationClass()
    {
        return $this->defaultTranslationClass;
    }

    /**
     * {@inheritDoc}
     */
    public function loadTranslations($object, $translationClass, $locale, $objectClass)
    {
        return $this->ea->loadTranslations($object, $translationClass, $locale, $objectClass);
    }

    /**
     * {@inheritDoc}
     */
    public function findTranslation(AbstractWrapper $wrapped, $locale, $field, $translationClass, $objectClass)
    {
        return $this->ea->findTranslation($wrapped, $locale, $field, $translationClass, $objectClass);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAssociatedTranslations(AbstractWrapper $wrapped, $transClass, $objectClass)
    {
        return $this->ea->removeAssociatedTranslations($wrapped, $transClass, $objectClass);
    }

    /**
     * {@inheritDoc}
     */
    public function insertTranslationRecord($translation)
    {
        return $this->ea->insertTranslationRecord($translation);
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslationValue($object, $field, $value = false)
    {
        return $this->ea->getTranslationValue($object, $field, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslationValue($object, $field, $value)
    {
        return $this->ea->setTranslationValue($object, $field, $value);
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->ea, $method], $args);
    }
}
