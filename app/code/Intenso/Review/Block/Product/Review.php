<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Product;

class Review extends \Magento\Review\Block\Product\Review
{
    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getProductReviewUrl()
    {
        $filterVarName = \Intenso\Review\Block\Html\Pager::FILTER_VAR_NAME;
        return $this->getUrl(
            'review/product/listAjax',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => $this->getProductId(),
                '_query' => ['rand' => rand(1, 1000000000), $filterVarName => $this->getRequest()->getParam($filterVarName)],
            ]
        );
    }
}
