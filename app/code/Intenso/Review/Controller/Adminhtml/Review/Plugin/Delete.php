<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Review\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Review delete controller plugin.
 */
class Delete
{
    /**
     * @var \Intenso\Review\Model\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Intenso\Review\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Intenso\Review\Model\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Intenso\Review\Model\ImageFactory $imageFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Intenso\Review\Model\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Intenso\Review\Model\ImageFactory $imageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageFactory = $imageFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Delete all images associated to the review being deleted
     *
     * @param \Magento\Review\Controller\Adminhtml\Product\Delete $subject
     * @param \Closure $proceed
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Review\Controller\Adminhtml\Product\Delete $subject, \Closure $proceed)
    {
        $reviewId = $subject->getRequest()->getParam('id', false);
        try {
            $images = $this->imageFactory->create()->load($reviewId, 'review_id')->getCollection();
            foreach ($images as $image) {
                $this->mediaDirectory->delete($this->mediaConfig->getMediaPath($image->getFile()));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $proceed();
    }
}
