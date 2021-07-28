<?php
namespace Veriteworks\Gmo\Controller\Card;

use \Magento\Customer\Controller\AbstractAccount;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Veriteworks\Gmo\Model\Card\Delete as Model;

/**
 * Delete registered card action
 */
class Delete extends AbstractAccount
{

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Model
     */
    private $delete;

    /**
     * @param Context $context
     * @param Model $delete
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Model $delete,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->delete = $delete;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (strtolower($this->_request->getMethod()) == 'post') {
            $this->_redirect('gmo/card/lists');
            return ;
        }

        try {
            $result = $this->delete->deleteCard($this->_request);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Failed to registering your card information.')
            );
        }

        $this->_redirect('gmo/card/lists');
    }
}
