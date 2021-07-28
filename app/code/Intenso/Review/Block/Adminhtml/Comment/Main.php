<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml review main block
 */
namespace Intenso\Review\Block\Adminhtml\Comment;

class Main extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize add new review
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('add');
        $this->_blockGroup = 'Intenso_Review';
        $this->_controller = 'adminhtml_comment';
    }
}
