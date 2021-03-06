<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
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
     * @var array
     */
    private $translations = [];

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea   = $this->getEventAdapter($eventArgs);
        $meta = $eventArgs->getClassMetadata();
        $name = $meta->getName();

        if (isset($this->translations[$name])) {
            $ea->mapTranslation($meta, $this->translations[$name]);
            unset($this->translations[$name]);
            return;
        }

        parent::loadClassMetadata($eventArgs);

        if (isset(static::$configurations[$this->name][$name])) {
            if ($name === $ea->getRootObjectClass($meta)) {
                $translationClass = static::$configurations[$this->name][$name]['translationClass'];
                $this->translations[$translationClass] = $name;
                // load translation class metadata if not loaded yet
                $ea->getObjectManager()->getClassMetadata($translationClass);
                $ea->mapTranslatable($meta, $translationClass);
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
