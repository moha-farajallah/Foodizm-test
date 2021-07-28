<?php
namespace Foula\Getsku\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    protected $_productRepository;

    public function __construct(
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        echo $this->_productRepository->getById($id)->getSku();        
    }

}
