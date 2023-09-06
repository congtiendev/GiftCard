<?php

namespace Mageplaza\Affiliate\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Mageplaza\Affiliate\Helper\SendEmail as SendEmail;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelperData;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class OrderWithAffiliate implements ObserverInterface
{
    protected AccountFactory $_accountFactory;
    protected HistoryFactory $_historyFactory;
    protected AffiliateHelperData $_helperData;
    protected CheckoutSession $_checkoutSession;
    protected PriceHelper $_priceHelper;
    protected SendEmail $_sendEmail;
    protected MessageManager $_messageManager;

    public function __construct(
        AccountFactory      $accountFactory,
        HistoryFactory      $historyFactory,
        PriceHelper         $priceHelper,
        AffiliateHelperData $helperData,
        SendEmail           $sendEmail,
        MessageManager      $messageManager,
        CheckoutSession     $checkoutSession
    )
    {
        $this->_accountFactory = $accountFactory;
        $this->_historyFactory = $historyFactory;
        $this->_helperData = $helperData;
        $this->_sendEmail = $sendEmail;
        $this->_priceHelper = $priceHelper;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $code = $this->_helperData->getAffiliateCode() ?? $this->_checkoutSession->getAffiliateCode() ?? null;
        $account = $this->_accountFactory->create()->load($code, 'code');
        if ($code && $account->getId() && $this->_helperData->isAffiliateEnabled()) {
            $order = $observer->getOrder();

            $commissionValue = $this->_helperData->getCommissionValue();
            $commissionType = $this->_helperData->getCommissionType();
            $commission = $this->_helperData->calculateAffiliate($order->getBaseSubtotal(), $commissionValue,
                $commissionType);
            if ($commission > 0) {
                try {
                    $account->setBalance($account->getBalance() + $commission)->save();
                    $history = $this->_historyFactory->create();
                    if ($this->_helperData->getApplyDiscount() !== 'no') {
                        $discount = $this->_helperData->calculateAffiliate($order->getBaseSubtotal(),
                            $this->_helperData->getDiscountValue(), $this->_helperData->getApplyDiscount());
                    }
                    $history->setData([
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'customer_id' => $account->getCustomerId(),
                        'amount' => $commission,
                        'discount' => $discount ?? 0,
                        'is_admin_change' => '0',
                        'status' => $order->getStatus(),
                    ]);
                    $this->updateHistory($history);
                    $this->_helperData->deleteAffiliateCode();
                    $this->_checkoutSession->unsAffiliateCode();
                } catch (\Exception $e) {
                    $this->_messageManager->addErrorMessage($e->getMessage());
                }
                $this->_sendEmail->sendEmail([
                    'mail_to' => $account->getAccountEmail($account->getId()),
                    'customer_name' => $account->getAccountName($account->getId()),
                    'order_id' => $order->getIncrementId(),
                    'commission' => $this->_priceHelper->currency($commission, true, false),
                    'balance' => $this->_priceHelper->currency($account->getBalance(), true, false),
                    'date' => $order->getCreatedAt(),
                ], 3);
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
