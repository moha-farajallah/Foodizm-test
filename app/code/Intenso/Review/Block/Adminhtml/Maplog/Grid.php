<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Adminhtml MAP log grid
 */
namespace Intenso\Review\Block\Adminhtml\Maplog;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * MAP action pager
     *
     * @var \Magento\Review\Helper\Action\Pager
     */
    protected $_mapActionPager = null;

    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Mail After Purchase (Map) model factory
     *
     * @var \Intenso\Review\Model\MapFactory
     */
    protected $_mapFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Intenso\Review\Model\MapFactory $mapFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Magento\Review\Helper\Action\Pager $mapActionPager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Intenso\Review\Model\ResourceModel\Map\CollectionFactory $mapFactory,
        \Intenso\Review\Helper\Data $reviewData,
        \Magento\Review\Helper\Action\Pager $mapActionPager,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_reviewData = $reviewData;
        $this->_mapFactory = $mapFactory;
        $this->_mapActionPager = $mapActionPager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('maplogGrid');
        $this->setDefaultSort('entity_id');
    }

    /**
     * Save search results
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $actionPager \Magento\Review\Helper\Action\Pager */
        $actionPager = $this->_mapActionPager;
        $actionPager->setStorageId('maplog');
        $actionPager->setItems($this->getCollection()->getResultingIds());

        return parent::_afterLoadCollection();
    }

    /**
     * Prepare collection
     *
     * @return \Intenso\Review\Block\Adminhtml\Maplog\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Intenso\Review\Model\ResourceModel\Map\Collection */
        $collection = $this->_mapFactory->create();

        $collection->appendOrderData();
        $collection->appendProductData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'filter_index' => 'entity_id',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'type' => 'datetime',
                'filter_index' => 'created_at',
                'index' => 'created_at',
                'align' => 'center',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'email_sent',
            [
                'header' => __('Status'),
                'type' => 'options',
                'options' => $this->_reviewData->getMapStatuses(),
                'filter_index' => 'email_sent',
                'index' => 'email_sent'
            ]
        );

        $this->addColumn(
            'sent_date',
            [
                'header' => __('Sent'),
                'type' => 'datetime',
                'filter_index' => 'sent_date',
                'index' => 'sent_date',
                'align' => 'center',
                'renderer' => 'Intenso\Review\Block\Adminhtml\Maplog\Grid\Renderer\SentDate',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('To'),
                'filter_index' => 'order.customer_email',
                'index' => 'customer_email',
                'type' => 'text',
                'align' => 'center'
            ]
        );

        $this->addColumn(
            'review_posted',
            [
                'header' => __('Review Submitted'),
                'type' => 'options',
                'options' => $this->_reviewData->getMapReviewPosted(),
                'filter_index' => 'review_posted',
                'index' => 'review_posted'
            ]
        );

        $this->addColumn(
            'review_date',
            [
                'header' => __('Review Date'),
                'type' => 'datetime',
                'filter_index' => 'review_date',
                'index' => 'review_date',
                'align' => 'center',
                'renderer' => 'Intenso\Review\Block\Adminhtml\Maplog\Grid\Renderer\ReviewDate',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order #'),
                'filter_index' => 'order.increment_id',
                'index' => 'increment_id',
                'type' => 'text',
                'align' => 'center'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'filter_index' => 'products.sku',
                'index' => 'sku',
                'type' => 'text',
                'align' => 'center'
            ]
        );

        $this->addColumn(
            'problem_error_code',
            [
                'header' => __('Error Code'),
                'index' => 'problem_error_code',
                'filter_index' => 'problem_error_code',
                'type' => 'text',
                'align' => 'center'
            ]
        );

        $this->addColumn(
            'problem_error_text',
            [
                'header' => __('Error Message'),
                'index' => 'problem_error_text',
                'filter_index' => 'problem_error_text',
                'type' => 'text',
                'truncate' => 100,
                'nl2br' => true,
                'escape' => true
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
}
