<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\Plugin;

class Review
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'intenso_product_review';

    /**
     * Return unique ID(s) for each object in system
     *
     * @param \Magento\Review\Model\Review $review
     * @return array
     */
    public function afterGetIdentities(\Magento\Review\Model\Review $review)
    {
        $tags = [];
        if ($review->getEntityPkValue()) {
            $tags[] = self::CACHE_TAG . '_' . $review->getEntityPkValue();
        }
        return $tags;
    }
}