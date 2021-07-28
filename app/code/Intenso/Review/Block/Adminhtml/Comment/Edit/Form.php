<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml Review Edit Form
 */
namespace Intenso\Review\Block\Adminhtml\Comment\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    private $reviewData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $reviewFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Helper\Data $reviewData,
        array $data = []
    ) {
        $this->reviewData = $reviewData;
        $this->customerRepository = $customerRepository;
        $this->productFactory = $productFactory;
        $this->reviewFactory = $reviewFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare edit review form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $comment = $this->_coreRegistry->registry('comment_data');
        $review = $this->reviewFactory->create()->load($comment->getReviewId());
        $product = $this->productFactory->create()->load($review->getEntityPkValue());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'intenso_review/*/save',
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
            'comment_details',
            ['legend' => __('Comment Details'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'review_title',
            'note',
            [
                'label' => __('Review'),
                'text' => '<a href="' . $this->getUrl(
                    'review/product/edit',
                    ['id' => $review->getId()]
                ) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
                    $review->getTitle()
                ) . '</a>'
            ]
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
            $customer = $this->customerRepository->getById($comment->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = __(
                '%1 <a href="mailto:%2">(%2)</a>',
                $this->escapeHtml($comment->getNickname()),
                $this->escapeHtml($comment->getGuestEmail())
            );
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

        if ($comment->getStatusId() == 4) {
            $confirmMsg = __('Please confirm to mark this comment as Not Spam');
            $hamUrl = $this->getUrl('*/*/ham', ['id' => $comment->getId()]);
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

        $fieldset->addField(
            'nickname',
            'text',
            ['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
        );

        $fieldset->addField(
            'text',
            'textarea',
            ['label' => __('Comment'), 'required' => true, 'name' => 'text', 'style' => 'height:16em;']
        );

        $form->setUseContainer(true);
        $form->setValues($comment->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
