<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml Review Edit Form
 */
namespace Intenso\Review\Block\Adminhtml\Review\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Core system store model
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Review Summary model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetaData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        array $data = []
    ) {
        $this->reviewData = $reviewData;
        $this->customerRepository = $customerRepository;
        $this->productFactory = $productFactory;
        $this->systemStore = $systemStore;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->productMetaData = $productMetaData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare edit review form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        $review = $this->_coreRegistry->registry('review_data');
        $product = $this->productFactory->create()->load($review->getEntityPkValue());
        $version = explode('.', $this->productMetaData->getVersion());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'intenso_review/review/save',
                        [
                            'id' => $this->getRequest()->getParam('id'),
                            'ret' => $this->_coreRegistry->registry('ret')
                        ]
                    ),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'review_details',
            ['legend' => __('Review Details'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'product_name',
            'note',
            [
                'label' => __('Product'),
                'text' => '<a href="' . $this->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getId()]
                ) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
                    $product->getName()
                ) . '</a>'
            ]
        );

        try {
            $customer = $this->customerRepository->getById($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            if ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $customerText = __('Administrator');
            } else {
                $reviewSummary = $this->reviewSummaryFactory->create()->load($review->getId());
                $customerText = __(
                    '%1 <a href="mailto:%2">(%2)</a>',
                    $this->escapeHtml($review->getNickname()),
                    $this->escapeHtml($reviewSummary->getGuestEmail())
                );
            }
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

        // the name of the following classes changed on 2.1.0 to fix a Less test error (MAGETWO-50126)
        if ($version[1] == 0) {
            $summaryRatingClass = 'summary_rating';
            $detailedRatingClass = 'detailed_rating';
        } else {
            $summaryRatingClass = 'summary-rating';
            $detailedRatingClass = 'detailed-rating';
        }
        $fieldset->addField(
            $summaryRatingClass,
            'note',
            [
                'label' => __('Summary Rating'),
                'text' => $this->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Rating\Summary')->toHtml()
            ]
        );

        $fieldset->addField(
            $detailedRatingClass,
            'note',
            [
                'label' => __('Detailed Rating'),
                'required' => true,
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    'Magento\Review\Block\Adminhtml\Rating\Detailed'
                )->toHtml() . '</div>'
            ]
        );

        if ($review->getStatusId() == 4) {
            $confirmMsg = __('Please confirm to mark this review as Not Spam');
            $hamUrl = $this->getUrl('intenso_review/review/ham', ['id' => $review->getId()]);
            $statusText = __('Spam');
            $statusText .= ' (<a href="'.$hamUrl.'" onclick="return confirm(\''.$confirmMsg.'\')">';
            $statusText .= __('Not Spam?');
            $statusText .= '</a>)';
            $fieldset->addField('status_id', 'note', ['label' => __('Status'), 'text' => $statusText]);
        } else {
            $fieldset->addField(
                'status_id',
                'select',
                [
                    'label' => __('Status'),
                    'required' => true,
                    'name' => 'status_id',
                    'values' => $this->reviewData->getReviewStatusesOptionArray()
                ]
            );
        }

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $review->setSelectStores($review->getStores());
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $review->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'nickname',
            'text',
            ['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
        );

        $fieldset->addField(
            'title',
            'text',
            ['label' => __('Summary of Review'), 'required' => true, 'name' => 'title']
        );

        $fieldset->addField(
            'detail',
            'textarea',
            ['label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:16em;']
        );

        $fieldset->addField(
            'comments',
            'note',
            [
                'label' => __('Store Owner Comment'),
                'text' => $this->getLayout()->createBlock('Intenso\Review\Block\Adminhtml\Review\Edit\Comment')->toHtml()
            ]
        );

        $fieldset->addField(
            'photos',
            'note',
            [
                'label' => __('Customer Photos'),
                'text' => $this->getLayout()->createBlock('Intenso\Review\Block\Adminhtml\Review\Edit\Photo')->toHtml()
            ]
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
