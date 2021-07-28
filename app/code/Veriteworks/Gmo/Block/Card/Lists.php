<?php
namespace Veriteworks\Gmo\Block\Card;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Card list block
 */
class Lists extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Veriteworks\Gmo\Model\Card\Lists
     */
    private $lists;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Veriteworks\Gmo\Model\Card\Lists $lists,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->currentCustomer = $currentCustomer;
        $this->lists = $lists;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('gmo/card/delete');
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            try {
                $id = $this->currentCustomer->getCustomerId();
                $customer = $this->customerRepository->getById($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * @return array
     */
    public function getCards()
    {
        $request = $this->getRequest();
        $storeId = $request->getParam('store_id', null);
        return $this->lists->loadRegisteredCards($storeId);
    }
}
