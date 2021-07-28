<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Intenso\Review\Model\Media;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Media config
 */
class Config extends \Magento\Catalog\Model\Product\Media\Config
{
    /**
     * @param string $file
     * @return string
     */
    public function getTmpMediaPath($file)
    {
        return $this->getBaseMediaPathAddition() . '/tmp/' . $this->_prepareFile($file);
    }


    /**
     * Filesystem directory path of review images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'reviews';
    }

    /**
     * Web-based directory path of review images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'reviews';
    }

    /**
     * @return string
     */
    public function getBaseMediaPath()
    {
        return 'reviews';
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'reviews';
    }
}
