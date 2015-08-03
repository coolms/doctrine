<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Uploadable\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

/**
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation to define that this object is uploadable manager
     */
    const UPLOADABLE_FILE       = 'CmsDoctrine\\Mapping\\Annotation\\UploadableFile';
    const UPLOADABLE_FILE_INFO  = 'CmsDoctrine\\Mapping\\Annotation\\UploadableFileInfo';
    const UPLOADABLE_MANAGER    = 'CmsDoctrine\\Mapping\\Annotation\\UploadableManager';

    /**
     * {@inheritDoc}
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);

        // class annotations
        if ($annot = $this->reader->getClassAnnotation($class, static::UPLOADABLE_MANAGER)) {
            if ($annot->fileInfo) {
                $config['uploadable']    = true;
                $config['fileInfoField'] = $annot->fileInfo->value;
            }

            if ($annot->file) {
                $config['fileField'] = $annot->file->value;
            }

            if ($annot->pathGenerator) {
                $config['pathGenerator'] = $annot->pathGenerator;
            }

            foreach ($class->getProperties() as $prop) {
                if (empty($config['fileInfoField'])
                    && $this->reader->getPropertyAnnotation($prop, static::UPLOADABLE_FILE_INFO)
                ) {
                    $config['fileInfoField'] = $prop->getName();
                }

                if (empty($config['fileField'])
                    && $this->reader->getPropertyAnnotation($prop, static::UPLOADABLE_FILE)
                ) {
                    $config['fileField'] = $prop->getName();
                }
            }

            $this->validateFullMetadata($meta, $config);
        }
    }
}
