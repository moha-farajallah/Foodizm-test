<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Block\Adminhtml\Comment;

/**
 * Review edit form
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Review action pager
     *
     * @var \Magento\Review\Helper\Action\Pager
     */
    protected $reviewActionPager = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Comment model factory
     *
     * @var \Intenso\Review\Model\CommentFactory
     */
    protected $commentFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Magento\Review\Helper\Action\Pager $reviewActionPager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Magento\Review\Helper\Action\Pager $reviewActionPager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->reviewActionPager = $reviewActionPager;
        $this->commentFactory = $commentFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit review
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'Intenso_Review';
        $this->_controller = 'adminhtml_comment';

        /** @var $actionPager \Magento\Review\Helper\Action\Pager */
        $actionPager = $this->reviewActionPager;
        $actionPager->setStorageId('comments');

        $commentId = $this->getRequest()->getParam('id');
        $prevId = $actionPager->getPreviousItemId($commentId);
        $nextId = $actionPager->getNextItemId($commentId);
        if ($prevId !== false) {
            $this->addButton(
                'previous',
                [
                    'label' => __('Previous'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('intenso_review/*/*', ['id' => $prevId]) . '\')'
                ],
                3,
                10
            );

            $this->addButton(
                'save_and_previous',
                [
                    'label' => __('Save and Previous'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['next_item' => $prevId]]],
                            ],
                        ],
                    ]
                ],
                3,
                11
            );
        }
        if ($nextId !== false) {
            $this->addButton(
                'save_and_next',
                [
                    'label' => __('Save and Next'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'save',
                                'target' => '#edit_form',
                                'eventData' => ['action' => ['args' => ['next_item' => $nextId]]],
                            ],
                        ],
                    ]
                ],
                3,
                100
            );

            $this->addButton(
                'next',
                [
                    'label' => __('Next'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('intenso_review/*/*', ['id' => $nextId]) . '\')'
                ],
                3,
                105
            );
        }
        $this->buttonList->update('save', 'label', __('Save Comment'));
        $this->buttonList->update('save', 'id', 'save_button');
        $this->buttonList->update('delete', 'label', __('Delete Comment'));

        if ($this->getRequest()->getParam($this->_objectId)) {
            $commentData = $this->commentFactory->create()->load($this->getRequest()->getParam($this->_objectId));
            $this->coreRegistry->register('comment_data', $commentData);
        }
    }

    /**
     * Get edit review header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $commentData = $this->coreRegistry->registry('comment_data');
        return __("Edit Comment '%1'", $this->escapeHtml($commentData->getTitle()));
    }
}
