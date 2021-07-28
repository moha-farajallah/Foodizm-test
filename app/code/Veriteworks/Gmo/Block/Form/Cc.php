<?php
namespace Veriteworks\Gmo\Block\Form;

use Magento\Framework\View\Element\Template;
use Veriteworks\Gmo\Model\Card\AdminLists;
use \Magento\Payment\Model\Config;

/**
 * Cc payment method form
 */
class Cc extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var string
     */
    protected $_template = 'Veriteworks_Gmo::form/cc.phtml';
    /**
     * @var string
     */
    protected $_path = 'payment/verite_gmo/';

    /**
     * @var AdminLists
     */
    private $list;

    /**
     * Form constructor.
     * @param AdminLists $list
     * @param Template\Context $context
     * @param Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        AdminLists $list,
        Template\Context $context,
        Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->list = $list;
    }

    /**
     * @return array|null
     */
    public function getRegisteredCards()
    {
        $request = $this->getRequest();
        $storeId = $request->getParam('store_id', null);
        return $this->list->loadRegisteredCards($storeId);
    }
}
