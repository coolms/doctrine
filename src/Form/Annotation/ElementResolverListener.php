<?php
/**
 * CoolMS2 Doctrine Module (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Form\Annotation;

use Zend\EventManager\AbstractListenerAggregate,
    Zend\EventManager\EventManagerInterface,
    Zend\Form\Annotation\ComposedObject,
    Doctrine\Common\Persistence\ObjectManager,
    DoctrineModule\Form\Element\ObjectSelect,
    DoctrineModule\Persistence\ProvidesObjectManager;

class ElementResolverListener extends AbstractListenerAggregate
{
    use ProvidesObjectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('configureElement', [$this, 'resolveComposedTargetObject'], PHP_INT_MAX);
        $this->listeners[] = $events->attach('configureElement', [$this, 'resolveObjectSelectTargetClass'], PHP_INT_MAX);
        $this->listeners[] = $events->attach('configureElement', [$this, 'handleHydratorAnnotation']);
    }

    /**
     * ComposedObject target object resolver
     *
     * Resolves the interface (specification) into entity object class
     *
     * @param \Zend\EventManager\EventInterface $e
     */
    public function resolveComposedTargetObject($e)
    {
        $annotation = $e->getParam('annotation');
        if (!$annotation instanceof ComposedObject) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        if (!isset($formSpec['object'])) {
            return;
        }

        $metadata = $this->objectManager->getClassMetadata($formSpec['object']);
        $fieldName = $e->getParam('elementSpec')['spec']['name'];

        $elementSpec = $e->getParam('elementSpec');
        unset($elementSpec['spec']['hydrator']);

        if ($metadata->hasAssociation($fieldName)) {
            $e->setParam('annotation', new ComposedObject([
                'value' => [
                    'target_object' => $metadata->getAssociationTargetClass($fieldName),
                    'is_collection' => $annotation->isCollection(),
                    'options'       => $annotation->getOptions(),
                ],
            ]));

            return;
        }

        if (!empty($metadata->embeddedClasses[$fieldName])) {
            $class = $metadata->embeddedClasses[$fieldName]['class'];
            if (!is_object($class)) {
                $class = $this->objectManager->getClassMetadata($class)->getName();
            }

            $e->setParam('annotation', new ComposedObject([
                'value' => [
                    'target_object' => $class,
                    'is_collection' => $annotation->isCollection(),
                    'options'       => $annotation->getOptions(),
                ],
            ]));
        }
    }

    /**
     * Handle the Hydrator annotation
     *
     * Removes the hydrator class from collection specification.
     *
     * @param  \Zend\EventManager\EventInterface $e
     * @return void
     */
    public function handleHydratorAnnotation($e)
    {
        $annotation = $e->getParam('annotation');
        if (!($annotation instanceof ComposedObject && $annotation->isCollection())) {
            return;
        }

        $elementSpec = $e->getParam('elementSpec');
        if (isset($elementSpec['spec']['hydrator'])) {
            unset($elementSpec['spec']['hydrator']);
        }
    }

    /**
     * ObjectSelect target class resolver
     *
     * Resolves the interface (specification) into entity object class
     *
     * @param \Zend\EventManager\EventInterface $e
     */
    public function resolveObjectSelectTargetClass($e)
    {
        $elementSpec = $e->getParam('elementSpec');
        if (!isset($elementSpec['spec']['type'])) {
            return;
        }

        $type = $elementSpec['spec']['type'];
        if (strtolower($type) !== 'objectselect' && !$type instanceof ObjectSelect) {
            return;
        }

        if (isset($elementSpec['spec']['options']['target_class']) &&
            class_exists($elementSpec['spec']['options']['target_class'])
        ) {
            return;
        }

        $formSpec = $e->getParam('formSpec');
        $metadata = $this->objectManager->getClassMetadata($formSpec['object']);

        if ($metadata->hasAssociation($elementSpec['spec']['name'])) {
            $elementSpec['spec']['options']['target_class']
                = $metadata->getAssociationTargetClass($elementSpec['spec']['name']);
        }
    }
}
