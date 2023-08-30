<?php

namespace Mageplaza\Affiliate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Affiliate\Helper\SendEmail as SendEmail;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelperData;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class OrderWithAffiliate implements ObserverInterface
{
    protected OrderFactory $_orderFactory;
    protected AccountFactory $_accountFactory;
    protected HistoryFactory $_historyFactory;
    protected AffiliateHelperData $_helperData;
    protected CheckoutSession $_checkoutSession;
    protected PriceHelper $_priceHelper;
    protected SendEmail $_sendEmail;
    protected MessageManager $_messageManager;
    protected OrderExtensionFactory $_orderExtensionFactory;

    public function __construct(
        OrderFactory          $orderFactory,
        AccountFactory        $accountFactory,
        HistoryFactory        $historyFactory,
        PriceHelper           $priceHelper,
        AffiliateHelperData   $helperData,
        SendEmail             $sendEmail,
        MessageManager        $messageManager,
        CheckoutSession       $checkoutSession,
        OrderExtensionFactory $orderExtensionFactory
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_accountFactory = $accountFactory;
        $this->_historyFactory = $historyFactory;
        $this->_helperData = $helperData;
        $this->_sendEmail = $sendEmail;
        $this->_priceHelper = $priceHelper;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderExtensionFactory = $orderExtensionFactory;
    }

    public function execute(Observer $observer)
    {
        $code = $this->_helperData->getAffiliateCode() ?? $this->_checkoutSession->getAffiliateCode() ?? null;
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
                    if ($this->_helperData->getApplyDiscount() !== 'No') {
                        $discount = $this->_helperData->calculateAffiliate($order->getSubtotal(), $this->_helperData->getDiscountValue(), $this->_helperData->getApplyDiscount());
                    }
                    $history->setData([
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'customer_id' => $order->getCustomerId(),
                        'amount' => $commission,
                        'discount' => $discount ?? 0,
                        'is_admin_change' => '0',
                        'status' => $order->getStatus(),
                    ]);
                    $this->updateHistory($history);
                    $this->_sendEmail->sendEmail();
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
}
