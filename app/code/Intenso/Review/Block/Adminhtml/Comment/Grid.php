<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Adminhtml comments grid
 */
namespace Intenso\Review\Block\Adminhtml\Comment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Comment action pager
     *
     * @var \Magento\Review\Helper\Action\Pager
     */
    protected $_commentActionPager = null;

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
     * Comment model factory
     *
     * @var \Intenso\Review\Model\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Magento\Review\Helper\Action\Pager $commentActionPager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Intenso\Review\Model\ResourceModel\Comment\CollectionFactory $commentFactory,
        \Intenso\Review\Helper\Data $reviewData,
        \Magento\Review\Helper\Action\Pager $commentActionPager,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_reviewData = $reviewData;
        $this->_commentFactory = $commentFactory;
        $this->_commentActionPager = $commentActionPager;
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
        $this->setId('commentGrid');
        $this->setDefaultSort('created_at');
    }

    /**
     * Save search results
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $actionPager \Magento\Review\Helper\Action\Pager */
        $actionPager = $this->_commentActionPager;
        $actionPager->setStorageId('comments');
        $actionPager->setItems($this->getCollection()->getResultingIds());

        return parent::_afterLoadCollection();
    }

    /**
     * Prepare collection
     *
     * @return \Intenso\Review\Block\Adminhtml\Comment\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Intenso\Review\Model\ResourceModel\Comment\Collection */
        $collection = $this->_commentFactory->create();

        // Remove spam comments from grid on default view (unfiltered view)
        $filters = $this->_backendHelper->prepareFilterString($this->getParam($this->getVarNameFilter(), null));
        if (!isset($filters['status'])) {
            $collection->excludeSpam();
        }

        $collection->appendReviewTitle();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for customer type
        if ($column->getId() == 'type') {
            if ($column->getFilter()->getValue() == 1) {
                $this->getCollection()->addFieldToFilter('main_table.customer_id', ['gt' => 0]);
            } elseif ($column->getFilter()->getValue() == 2) {
                $this->getCollection()->addFieldToFilter('main_table.customer_id', ['is' => new \Zend_Db_Expr('NULL')]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
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
            'status',
            [
                'header' => __('Status'),
                'type' => 'options',
                'options' => $this->_reviewData->getReviewStatuses(),
                'filter_index' => 'status_id',
                'index' => 'status_id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Review Title'),
                'filter_index' => 'rdt.title',
                'index' => 'title',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'nickname',
            [
                'header' => __('Nickname'),
                'filter_index' => 'main_table.nickname',
                'index' => 'nickname',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'text',
            [
                'header' => __('Comment'),
                'index' => 'text',
                'filter_index' => 'text',
                'type' => 'text',
                'truncate' => 50,
                'nl2br' => true,
                'escape' => true
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'type' => 'select',
                'index' => 'type',
                'filter' => 'Intenso\Review\Block\Adminhtml\Comment\Grid\Filter\Type',
                'renderer' => 'Intenso\Review\Block\Adminhtml\Comment\Grid\Renderer\Type'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getEntityId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'intenso_review/comment/edit',
                        ],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid mass actions
     *
     * @return void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->setMassactionIdFilter('rt.entity_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('comments');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    '*/*/massDelete',
                    []
                ),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_reviewData->getReviewStatusesOptionArray();
        array_unshift($statuses, ['label' => '', 'value' => '']);
        $this->getMassactionBlock()->addItem(
            'update_status',
            [
                'label' => __('Update Status'),
                'url' => $this->getUrl(
                    '*/*/massUpdateStatus',
                    []
                ),
                'additional' => [
                    'status' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses,
                    ],
                ]
            ]
        );
    }

    /**
     * Get row url
     *
     * @param \Magento\Review\Model\Review|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'intenso_review/comment/edit',
            [
                'id' => $row->getEntityId()
            ]
        );
    }
}
