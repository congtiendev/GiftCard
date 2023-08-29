<?php

namespace Mageplaza\Affiliate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelperData;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class OrderWithAffiliate implements ObserverInterface
{
    protected OrderFactory $_orderFactory;
    protected AccountFactory $_accountFactory;
    protected HistoryFactory $_historyFactory;
    protected CheckoutSession $_checkoutSession;
    protected AffiliateHelperData $_helperData;
    protected MessageManager $_messageManager;

    public function __construct(
        OrderFactory        $orderFactory,
        AccountFactory      $accountFactory,
        HistoryFactory      $historyFactory,
        CheckoutSession     $checkoutSession,
        AffiliateHelperData $helperData,
        MessageManager      $messageManager
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_accountFactory = $accountFactory;
        $this->_historyFactory = $historyFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_helperData = $helperData;
        $this->_messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        $code = $this->_helperData->getAffiliateCode();
        $account = $this->_accountFactory->create()->load($code, 'code');
        if ($code && $account->getId() && $this->_helperData->isAffiliateEnabled()) {
            $order = $observer->getOrder();

            $commissionValue = $this->_helperData->getCommissionValue();
            $commissionType = $this->_helperData->getCommissionType();
            $commission = $this->_helperData->calculateAffiliate($order->getSubtotal(), $commissionValue, $commissionType);
            if ($commission > 0) {
                try {
                    $account->setBalance($account->getBalance() + $commission)->save();
                    $history = $this->_historyFactory->create();
                    $history->setData([
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'customer_id' => $order->getCustomerId(),
                        'amount' => $commission,
                        'is_admin_change' => '0',
                        'status' => $order->getStatus(),
                    ]);
                    $this->updateHistory($history);
                    if ($this->_helperData->getApplyDiscount() !== 'No') {
                        $discount = $this->_helperData->calculateAffiliate($order->getSubtotal(), $this->_helperData->getDiscountValue(), $this->_helperData->getApplyDiscount());
                    }
                    
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info(json_encode($discount));
                    $logger->info(json_encode($commission));

                    $this->addAffiliateOrderTotal($order->getId(), $discount ?? 0, $commission ?? 0);
                    $this->_helperData->deleteAffiliateCode();
                } catch (\Exception $e) {
                    $this->_messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
    }

    public function updateHistory($history): void
    {
        try {
            $history->save();
        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());
        }
    }

    public function addAffiliateOrderTotal($orderId, $discount, $commission): void
    {
        $orderTotals = $this->_orderFactory->create()->load($orderId);
        $orderTotals->addData([
            'affiliate_discount' => $discount,
            'affiliate_commission' => $commission,
        ]);
        try {
            $orderTotals->save();

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(json_encode($orderTotals->getData()));

        } catch (\Exception $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());
        }
    }

}