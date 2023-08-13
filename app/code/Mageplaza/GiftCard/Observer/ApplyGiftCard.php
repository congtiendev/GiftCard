<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class ApplyGiftCard implements ObserverInterface
{
    protected MessageManager $messageManager;
    protected CheckoutSession $checkoutSession;

    public function __construct(
        MessageManager  $messageManager,
        CheckoutSession $checkoutSession
    )
    {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $couponCode = $observer->getRequest()->getParam('coupon_code');
        $giftCardCode = "CONGTIENDEV";
        if ($couponCode === $giftCardCode) {
            $this->messageManager->addSuccessMessage(__('Gift card code applied successfully.'));
            $controller->getRequest()->setParam('remove', 1);
        } else {
            $this->messageManager->getMessages(true)->deleteMessageByIdentifier('coupon');
        }
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(json_encode($couponCode));
    }
}