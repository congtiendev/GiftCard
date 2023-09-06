<?php

namespace Mageplaza\Affiliate\Controller\Refer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote;


class Index extends Action
{
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected CheckoutSession $checkoutSession;
    protected $resultRedirect;
    protected Cart $cart;
    protected Quote $quote;

    public function __construct(
        Context         $context,
        Cart            $cart,
        Quote           $quote,
        HelperData      $helperData,
        AccountFactory  $accountFactory,
        CheckoutSession $checkoutSession,
        ResultFactory   $resultFactory
    )
    {
        parent::__construct($context);
        $this->cart = $cart;
        $this->quote = $quote;
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
        $this->checkoutSession = $checkoutSession;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {

        if (!$this->helperData->isAffiliateEnabled()) {
            $this->messageManager->addErrorMessage(__('Affiliate is disabled !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }

        $couponCode = $this->cart->getQuote()->getCouponCode() ?? $this->checkoutSession->getAffiliateCode() ?? null;
        if ($couponCode) {
            $this->messageManager->addErrorMessage(__('You have already been used coupon code %1', $couponCode . ' ! Please cancel it to refer another account'));
            return $this->resultRedirect->setPath('affiliate/history/index');
        }

        $code = $this->getRequest()->getParam($this->helperData->getUrlKey());
        $account = $this->accountFactory->create()->load($code, 'code');
        if (!$code || !$account->getId()) {
            $this->messageManager->addErrorMessage(__('Refer link is not valid !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }

        if ($account->getId() && $account->getStatus() != 1) {
            $this->messageManager->addErrorMessage(__('This account has not been activated !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }
        $referenceByName = $account->getAccountName($account->getId());
        if ($this->helperData->getAffiliateCode()) {
            $this->messageManager->addNoticeMessage(__('You have already been referred by %1', $referenceByName));
            return $this->resultRedirect->setPath('affiliate/history/index');
        }

        $this->helperData->setAffiliateCode($code);
        $this->messageManager->addSuccessMessage(__('You are referred by %1', $referenceByName));
        return $this->resultRedirect->setPath('affiliate/history/index');
    }
}