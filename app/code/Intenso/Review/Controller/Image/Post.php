<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Image;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Post extends Action
{
    /**
     * Maximum file size allowed in bytes
     */
    const MAX_FILE_SIZE = 10000000;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_filesystem = $filesystem;
    }

    /**
     * Save uploaded images to tmp file
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $image = $this->getRequest()->getFiles('image');
        if (isset($image['tmp_name']) && strlen($image['tmp_name']) > 0) {
            try {
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'image']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $isValidFileSize = $this->_validateFileSize($uploader->getFileSize(), self::MAX_FILE_SIZE);
                if (!$isValidFileSize) {
                    $responseContent = [
                        'success' => false,
                        'message' => __('The photo must be less than %1M.', self::MAX_FILE_SIZE)
                    ];
                } else {
                    $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('reviews/tmp/');

                    $result = $uploader->save($path);

                    $responseContent = [
                        'success' => true,
                        'file' => $result['file'],
                        'originalName' => $image['name']
                    ];
                }
            } catch (\Exception $e) {
                $responseContent = [
                    'success' => false,
                    'message' => __('There was a problem uploading the image, please try again.'),
                ];
            }
            $resultJson->setData($responseContent);
            return $resultJson;
        }
    }

    /**
     * Validate max file size
     *
     * @param int $fileSize
     * @param int $maxFileSize
     * @return bool
     */
    protected function _validateFileSize($fileSize, $maxFileSize)
    {
        if ($fileSize > $maxFileSize) {
            return false;
        }
        return true;
    }
}
