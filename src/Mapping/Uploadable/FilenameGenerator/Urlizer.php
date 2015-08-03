<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Uploadable\FilenameGenerator;

use Behat\Transliterator\Transliterator,
    Gedmo\Uploadable\FilenameGenerator\FilenameGeneratorInterface;

/**
 * Urlizer filename generator
 *
 * This class generates a filename
 *
 * @author Dmitry Popov <d.popov@altgrapphic.com>
 */

class Urlizer implements FilenameGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public static function generate($filename, $extension, $object = null)
    {
        // Step 1: transliteration, changing 北京 to 'Bei Jing'
        $filename = Transliterator::transliterate($filename);

        // Step 2: urlization (replace spaces by '-' etc...)
        $filename = Transliterator::urlize($filename) . $extension;

        // Done!
        return $filename;
    }
}
