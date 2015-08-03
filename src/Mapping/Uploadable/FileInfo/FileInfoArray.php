<?php
/**
 * CoolMS2 Doctrine Common Library (http://www.coolms.com/)
 *
 * @link      http://github.com/coolms/doctrine for the canonical source repository
 * @copyright Copyright (c) 2006-2015 Altgraphic, ALC (http://www.altgraphic.com)
 * @license   http://www.coolms.com/license/new-bsd New BSD License
 * @author    Dmitry Popov <d.popov@altgraphic.com>
 */

namespace CmsDoctrine\Mapping\Uploadable\FileInfo;

use Gedmo\Uploadable\FileInfo\FileInfoArray as BaseFileInfoArray;

/**
 * FileInfoArray
 *
 * @author Dmitry Popov <d.popov@altgraphic.com>
 */

class FileInfoArray extends BaseFileInfoArray
{
    /**
     * @return string
     */
    public function getName()
    {
        if (null === $this->isUploadedFile()) {
            return basename($this->getTmpName());
        }

        return parent::getName();
    }

    /**
     * @return string
     */
    public function getOrigName()
    {
        return $this->fileInfo['name'];
    }

    /**
     * @return string
     */
    public function getMd5Hash()
    {
        return md5_file($this->getTmpName());
    }

    /**
     * @return boolean
     */
    public function isUploadedFile()
    {
        if (array_key_exists('is_uploaded_file', $this->fileInfo)) {
            return $this->fileInfo['is_uploaded_file'];
        }

        return is_uploaded_file($this->getTmpName());
    }
}
