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

use CmsCommon\Mapping\Common\ContentableInterface,
    CmsCommon\Mapping\Common\IdentifiableInterface,
    CmsCommon\Mapping\Common\ObjectableInterface;

/**
 * Interface for the translation entity
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
interface TranslationInterface extends IdentifiableInterface, ObjectableInterface, ContentableInterface
{
    /**
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set field
     *
     * @param string $field
     */
    public function setField($field);

    /**
     * Get field
     *
     * @return string
     */
    public function getField();
}
