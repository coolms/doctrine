<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Translatable\Mapping\Event;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Translatable\Mapping\Event\TranslatableAdapter;

/**
 * Doctrine event adapter interface for Translatable behavior
 */
interface TranslatableAdapterInterface extends TranslatableAdapter
{
    /**
     * @param ClassMetadata $meta
     * @param string $translationClassName
     */
    public function mapTranslatable(ClassMetadata $meta, $translationClassName);

    /**
     * @param ClassMetadata $meta
     * @param string $translatableClassName
     */
    public function mapTranslation(ClassMetadata $meta, $translatableClassName);
}
