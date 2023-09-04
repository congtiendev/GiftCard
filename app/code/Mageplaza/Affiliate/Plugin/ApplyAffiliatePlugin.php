<?php

namespace Mageplaza\Affiliate\Plugin;

use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Block\Cart\Coupon as CouponBlock;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;


class ApplyAffiliatePlugin
{
    protected AffiliateHelper $helperData;
    protected CheckoutSession $checkoutSession;
    protected AccountFactory $accountFactory;
    protected HistoryFactory $historyFactory;

    public function __construct(
        AffiliateHelper $helperData,
        CheckoutSession $checkoutSession,
        AccountFactory  $accountFactory,
        HistoryFactory  $historyFactory)
    {
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
        $this->historyFactory = $historyFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetCouponCode(CouponBlock $subject, $result)
    {
        if (!$this->helperData->isAffiliateEnabled() || $this->helperData->getApplyDiscount() === 'no') {
            return $result;
        }
        $couponCode = $this->checkoutSession->getAffiliateCode();
        $affiliateCode = $this->accountFactory->create()->load($couponCode, 'code')->getCode();
        if (!$affiliateCode) {
            return $result; // Return default coupon code
        }
        return $affiliateCode;
    }
}