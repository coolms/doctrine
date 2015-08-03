<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Uploadable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\Event\AdapterInterface,
    Gedmo\Uploadable\FileInfo\FileInfoInterface,
    Gedmo\Uploadable\UploadableListener,
    CmsCommon\Stdlib\FileUtils;

/**
 * Uploadable event subscriber
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */
class UploadableSubscriber extends UploadableListener
{
    /**
     * {@inheritDoc}
     */
    public function moveFile(
        FileInfoInterface $fileInfo,
        $path,
        $filenameGeneratorClass = false,
        $overwrite = false,
        $appendNumber = false,
        $object
    ) {
        if (null === $fileInfo->isUploadedFile()) {
            $file = $fileInfo->getTmpName();
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $this->getRelativePath($file));
        }

        $info = parent::moveFile($fileInfo, $path, $filenameGeneratorClass, $overwrite, $appendNumber, $object);

        if (null === $fileInfo->isUploadedFile()) {
            $info['origFileName'] = $fileInfo->getOrigName();
        }

        return $info;
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getRelativePath($filePath)
    {
        return FileUtils::relativePath(getcwd(), realpath(dirname($filePath)));
    }

    /**
     * {@inheritDoc}
     */
    public function doMoveFile($source, $dest, $isUploadedFile = null)
    {
        if (null === $isUploadedFile) {
            return true;
        }

        return parent::doMoveFile($source, $dest, $isUploadedFile);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFile($filePath)
    {
        // Disallow file removals
    }
}
