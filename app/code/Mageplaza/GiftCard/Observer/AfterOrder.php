<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

class AfterOrder implements ObserverInterface
{
    protected $_amountUsed = 0;
    protected $_amount = 0;
    protected CheckoutSession $_checkoutSession;
    protected GiftCardFactory $_giftCardFactory;
    protected GiftCardHistoryFactory $_giftCardHistoryFactory;
    protected LoggerInterface $_logger;

    public function __construct(
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        GiftCardHistoryFactory $giftCardHistoryFactory,
        LoggerInterface        $logger
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_giftCardFactory = $giftCardFactory;
        $this->_giftCardHistoryFactory = $giftCardHistoryFactory;
        $this->_logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $giftCardHistory = $this->_giftCardHistoryFactory->create();
        $giftCard = $this->_giftCardFactory->create()->load($this->_checkoutSession->getCode(), 'code');

        if (!$giftCard->getId()) {
            return;
        }

        // Calculate amount used
        if ($this->_checkoutSession->getAmount() > $order->getSubtotal()) {
            $this->_amountUsed = $order->getSubtotal() + $giftCard->getAmountUsed();
            $this->_amount = $order->getSubtotal();
        } else {
            $this->_amountUsed = $giftCard->getBalance();
            $this->_amount = $order->getSubtotal() - $giftCard->getAmountUsed();
        }

        try {
            $giftCard->setAmountUsed($this->_amountUsed)->save();
            // if user is logged in, save gift card history
            if ($order->getCustomerId()) {
                $giftCardHistory->load($order->getCustomerId(), 'customer_id');
                $data = [
                    'giftcard_id' => $giftCard->getId(),
                    'customer_id' => $order->getCustomerId(),
                    'amount' => '- ' . $this->_amount,
                    'action' => 'Use for Order #' . $order->getIncrementId()
                ];
                $giftCardHistory->setData($data);
            }
            $giftCardHistory->save();
            // Unset applied gift card
            $this->_checkoutSession->unsCode();
            $this->_checkoutSession->unsAmount();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
