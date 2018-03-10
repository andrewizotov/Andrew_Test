<?php
namespace Andrew\Test\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class SalesOrderChangeStatus implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**#@+
     * Constants
     */
    const SAVED_GROUPS_PATH = 'extra_settings/andrew_test/customer_groups';


    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();

        if ($this->_isCustomerGroupWithinConfiguration($order->getCustomerGroupId())) {
             if ($order->canHold()) {
                 $order->hold();
                 $this->_logger->info( "Order Id:".$order->getIncrementId()." set status onHold" );
             } else {
                 $this->_logger->info( "Order Id:".$order->getIncrementId()." can't be onOold " );
             }
        }
    }

    /**
     * Check if current customer groupId defined in settings
     * @param   integer $customerGroupId
     * @return  bool
     */
    protected function _isCustomerGroupWithinConfiguration($customerGroupId)
    {
        $savedCustomerGroupIds = explode(",", $this->_scopeConfig->getValue(self::SAVED_GROUPS_PATH));

        if (in_array($customerGroupId, $savedCustomerGroupIds) ) {
            return true;
        }

        return false;
    }
}