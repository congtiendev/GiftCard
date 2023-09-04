<?php

namespace Mageplaza\Affiliate\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ActionInterface;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\Affiliate\Helper\SendEmail as SendEmail;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelperData;

class ApplyAffiliate implements ObserverInterface
{
    protected SendEmail $sendEmail;
    protected ActionFlag $actionFlag;
    protected RedirectInterface $redirect;
    protected AccountFactory $accountFactory;
    protected HistoryFactory $historyFactory;

    protected ManagerInterface $messageManager;
    protected CheckoutSession $checkoutSession;
    protected AffiliateHelperData $affiliateHelperData;

    public function __construct(
        RedirectInterface   $redirect,
        ActionFlag          $actionFlag,
        ManagerInterface    $messageManager,
        CheckoutSession     $checkoutSession,
        AccountFactory      $accountFactory,
        HistoryFactory      $historyFactory,
        SendEmail           $sendEmail,
        AffiliateHelperData $affiliateHelperData
    )
    {
        $this->redirect = $redirect;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->accountFactory = $accountFactory;
        $this->historyFactory = $historyFactory;
        $this->checkoutSession = $checkoutSession;
        $this->sendEmail = $sendEmail;
        $this->affiliateHelperData = $affiliateHelperData;
    }

    public function execute(Observer $observer)
    {
        if (!$this->affiliateHelperData->isAffiliateEnabled() || $this->affiliateHelperData->getApplyDiscount() === 'No') {
            return $this;
        }
        $controller = $observer->getData('controller_action');

        if ($this->affiliateHelperData->getAffiliateCode()) {
            $this->messageManager->addWarningMessage('You are enjoying incentives from this affiliate code. Please cancel the referent to continue entering the code 😊');
            $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
        } else {
            $couponCode = trim($controller->getRequest()->getParam('coupon_code'));
            $affiliateCode = $this->accountFactory->create()->load($couponCode, 'code');
            $remove = $controller->getRequest()->getParam('remove');

            if ($affiliateCode->getId()) {
                if (!$remove) {
                    $this->checkoutSession->setAffiliateCode($couponCode);
                    $this->messageManager->addSuccessMessage(__('Affiliate code applied successfully 💲🤑'));
                    $this->actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
                    $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
                } else {
                    $this->checkoutSession->unsAffiliateCode();
                }
            } else if ($remove && $this->checkoutSession->getAffiliateCode()) {
                $this->checkoutSession->unsAffiliateCode();
                $this->messageManager->addSuccessMessage(__('You canceled the affiliate code. 🎁🎁🎁'));
            }
        }
        return $this;
    }
}