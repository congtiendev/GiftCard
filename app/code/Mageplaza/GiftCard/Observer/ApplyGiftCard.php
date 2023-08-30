<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ActionInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelperData;
use Magento\Checkout\Model\Session as CheckoutSession;

class ApplyGiftCard implements ObserverInterface
{
    protected ActionFlag $actionFlag;
    protected RedirectInterface $redirect;

    protected ManagerInterface $messageManager;
    protected CheckoutSession $checkoutSession;
    protected GiftCardFactory $giftcardFactory;
    protected GiftCardHelperData $giftCardHelperData;

    public function __construct(
        RedirectInterface  $redirect,
        ActionFlag         $actionFlag,
        ManagerInterface   $messageManager,
        CheckoutSession    $checkoutSession,
        GiftCardFactory    $giftcardFactory,
        GiftCardHelperData $giftCardHelperData
    )
    {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->giftcardFactory = $giftcardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->giftCardHelperData = $giftCardHelperData;
    }

    public function execute(Observer $observer)
    {
        if (!$this->giftCardHelperData->allowUsedGiftCardAtCheckout() || !$this->giftCardHelperData->isGiftCardEnabled()) {
            return $this;
        }
        $controller = $observer->getData('controller_action');
        $couponCode = trim($controller->getRequest()->getParam('coupon_code'));
        $remove = $controller->getRequest()->getParam('remove');
        $giftCard = $this->giftcardFactory->create()->load($couponCode, 'code');

        if ($giftCard->getId()) {
            if (!$remove) {
                $this->applyGiftCard($giftCard, $couponCode);
                $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
            } else {
                $this->checkoutSession->unsCode();
            }
        } else if ($remove && $this->checkoutSession->getCode()) {
            $this->checkoutSession->unsCode();
            $this->messageManager->addSuccessMessage(__('You canceled the gift card. 🎁🎁🎁'));
        }
    }

    protected function applyGiftCard($giftCard, $couponCode): void
    {
        $amount = $giftCard->getBalance() - $giftCard->getAmountUsed();
        if ($amount > 0) {
            $this->checkoutSession->setCode($couponCode);
            $this->messageManager->addSuccessMessage(__('Gift code applied successfully 💲🤑'));
        } else {
            $this->messageManager->addErrorMessage(__('Gift Card has expired or fully used. 💸💸💸'));
        }
        $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
    }
}
