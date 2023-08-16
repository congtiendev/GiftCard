<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ActionInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class ApplyGiftCard implements ObserverInterface
{
    protected ActionFlag $actionFlag;
    protected RedirectInterface $redirect;
    protected ManagerInterface $messageManager;
    protected CheckoutSession $checkoutSession;
    protected GiftCardFactory $giftcardFactory;

    public function __construct(
        RedirectInterface $redirect,
        ActionFlag        $actionFlag,
        ManagerInterface  $messageManager,
        CheckoutSession   $checkoutSession,
        GiftCardFactory   $giftcardFactory
    )
    {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->giftcardFactory = $giftcardFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $this->checkCouponCode($observer);
    }

    public function checkCouponCode(Observer $observer)
    {
        $controller = $observer->getData('controller_action');
        $couponCode = $controller->getRequest()->getParam('coupon_code');
        $remove = $controller->getRequest()->getParam('remove');
        $giftCard = $this->giftcardFactory->create()->load($couponCode, 'code');
        if ($giftCard->getId()) {
            if (!$remove) {
                $amount = $giftCard->getBalance() - $giftCard->getAmountUsed();
                if ($amount > 0) {
                    $this->checkoutSession->setCode($couponCode); // Lưu code vào session
                    $this->checkoutSession->setAmount($amount);
                    $this->messageManager->addSuccessMessage(__('Gift code applied successfully 💲🤑'));
                } else {
                    $this->messageManager->addErrorMessage(__('Gift Card has expired or fully used. 💸💸💸'));
                }
                $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
                // Chuyển hướng về trang giỏ hàng
                $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
            } else {
                $this->checkoutSession->unsCode();
                $this->checkoutSession->unsAmount();
            }
        } else {
            if ($remove && $this->checkoutSession->getCode()) {
                $this->checkoutSession->unsCode();
                $this->checkoutSession->unsAmount();
                $this->messageManager->addSuccessMessage(__('You canceled the gift card. 🎁🎁🎁'));
            }asdasdas
        }
    }
}
