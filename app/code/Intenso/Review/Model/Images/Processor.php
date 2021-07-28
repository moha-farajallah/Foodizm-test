<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Model\Images;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Review images processor.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Processor
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
     * @var \Intenso\Review\Helper\Image
     */
    protected $imageHelper;

    /**
     * @param \Intenso\Review\Model\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Intenso\Review\Helper\Image $imageHelper
     */
    public function __construct(
        \Intenso\Review\Model\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Intenso\Review\Helper\Image $imageHelper
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageHelper = $imageHelper;
    }

    /**
     * Move temporary images to media path and delete them after that
     * Rotate image when required, if exif info is available
     *
     * @param string $file
     * @return string
     */
    public function duplicateImageFromTmp($file)
    {
        // move temp image to media folder
        $destinationFile = $this->getUniqueFileName($file);
        $this->mediaDirectory->copyFile(
            $this->mediaConfig->getTmpMediaPath($file),
            $this->mediaConfig->getMediaPath($destinationFile)
        );
        $this->mediaDirectory->delete($this->mediaConfig->getTmpMediaPath($file));

        // rotate image when required, if exif info is available
        try {
            $fileAbsolutePath = $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaPath($destinationFile));
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($fileAbsolutePath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $this->imageHelper->rotate($fileAbsolutePath, 180);
                            break;
                        case 6:
                            $this->imageHelper->rotate($fileAbsolutePath, -90);
                            break;
                        case 8:
                            $this->imageHelper->rotate($fileAbsolutePath, 90);
                            break;
                        default:
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            // exif data not supported for this image format
        }

        return str_replace('\\', '/', $destinationFile);
    }

    /**
     * Get unique name
     *
     * @param string $file
     * @return string
     */
    protected function getUniqueFileName($file)
    {
        $newFilename = md5($file . microtime());
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        $destinationFile = dirname($file) . '/' . $newFilename . '.' . $fileExtension;

        return $destinationFile;
    }
}
