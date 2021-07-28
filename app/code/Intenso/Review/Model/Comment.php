<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Comment extends AbstractModel implements IdentityInterface
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reviewFactory = $reviewFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\Comment');
    }

    /**
     * Validate comment form fields
     *
     * @param bool $isLoggedIn
     * @return bool|string[]
     */
    public function validate($isLoggedIn = false)
    {
        $errors = [];

        if (!$isLoggedIn && !\Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a nickname.');
        }

        if (!\Zend_Validate::is($this->getComment(), 'NotEmpty')) {
            $errors[] = __('Please enter a comment.');
        }

        if (!$isLoggedIn && !\Zend_Validate::is($this->getEmail(), 'NotEmpty')) {
            $errors[] = __('Please enter your email.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $tags = [];
        $reviewFactory = $this->reviewFactory->create()->load($this->getReviewId());
        if ($reviewFactory->getEntityPkValue()) {
            $tags[] = \Intenso\Review\Model\Plugin\Review::CACHE_TAG . '_' . $reviewFactory->getEntityPkValue();
        }
        return $tags;
    }
}
