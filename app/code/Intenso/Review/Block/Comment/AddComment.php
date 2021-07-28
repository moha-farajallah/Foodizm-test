<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Comment;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Url;

class AddComment extends \Magento\Framework\View\Element\Template
{
    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    ) {
        $this->urlEncoder = $urlEncoder;
        $this->reviewData = $reviewData;
        $this->httpContext = $httpContext;
        $this->customerUrl = $customerUrl;
        parent::__construct($context, $data);
    }

    /**
     * Check if user can write comment
     *
     * @return bool
     */
    public function getAllowWriteReviewFlag()
    {
        return ($this->httpContext->getValue(Context::CONTEXT_AUTH)
            || $this->reviewData->getIsGuestAllowToWrite()
        );
    }

    /**
     * Get comment post action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl(
            'intenso_review/comment/addcomment',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id' => '__review_id_placeholder__',
            ]
        );
    }

    /**
     * Return login URL
     *
     * @return string
     */
    public function getLoginLink()
    {
        $queryParam = $this->urlEncoder->encode(
            $this->getUrl('*/*/*', ['_current' => true])
        );
        return $this->getUrl('customer/account/login/', [Url::REFERER_QUERY_PARAM_NAME => $queryParam]);
    }

    /**
     * Return register URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->customerUrl->getRegisterUrl();
    }
}
