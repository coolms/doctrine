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

use Gedmo\Translatable\Translatable,
    CmsDoctrineORM\Mapping\Translatable\MappedSuperclass\AbstractTranslation;

/**
 * Interface for the translatable entity
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
interface TranslatableInterface extends Translatable
{
    /**
     * @return AbstractTranslation[]
     */
    public function getTranslations();

    /**
     * @param AbstractTranslation[] $translations
     */
    public function setTranslations($translations);

    /**
     * @param AbstractTranslation[] $translations
     */
    public function addTranslations($translations);

    /**
     * @param AbstractTranslation $translation
     */
    public function addTranslation(AbstractTranslation $translation);

    /**
     * @param AbstractTranslation[] $translations
     */
    public function removeTranslations($translations);

    /**
     * @param AbstractTranslation $translation
     */
    public function removeTranslation(AbstractTranslation $translation);

    /**
     * Removes all translations
     */
    public function clearTranslations();

    /**
     * @param string|\CmsLocale\Mapping\LocaleInterface $locale
     */
    public function setTranslatableLocale($locale);

    /**
     * @return string
     */
    public function getTranslatableLocale();
}
