<?php


namespace Veriteworks\Gmo\Controller\Card;

use \Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\CsrfAwareActionInterface;
use \Magento\Framework\App\Action\HttpPostActionInterface;
use \Magento\Framework\App\Request\InvalidRequestException;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Phrase;
use \Magento\Framework\Controller\Result\Redirect;

/**
 * Edit post registered card action
 */
class EditPost extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Veriteworks\Gmo\Model\Card\Add
     */
    private $edit;

    /**
     * @param Context $context
     * @param \Veriteworks\Gmo\Model\Card\Add $edit
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Veriteworks\Gmo\Model\Card\Add $edit,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->edit = $edit;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (strtolower($this->_request->getMethod()) != 'post') {
            $this->_redirect('gmo/card/lists');
            return ;
        }

        try {
            $result = $this->edit->addCard($this->_request);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Failed to registering your card information.')
            );
        }

        $this->_redirect('gmo/card/lists');
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
