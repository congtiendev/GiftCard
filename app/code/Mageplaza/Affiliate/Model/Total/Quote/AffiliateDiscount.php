<?php

namespace Mageplaza\Affiliate\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class AffiliateDiscount extends AbstractTotal
{
    protected HelperData $_helperData;
    protected AccountFactory $_accountFactory;
    protected CheckoutSession $_checkoutSession;
    protected PriceCurrencyInterface $_priceCurrency;


    public function __construct(
        HelperData             $helperData,
        AccountFactory         $accountFactory,
        CheckoutSession        $checkoutSession,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->_helperData = $helperData;
        $this->_accountFactory = $accountFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
    }

    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    ): AffiliateDiscount
    {
        $code = $this->_helperData->getAffiliateCode() ?? $this->_checkoutSession->getAffiliateCode() ?? null;
        $account = $this->_accountFactory->create()->load($code, 'code');
        if ($account->getId() && $this->_helperData->isAffiliateEnabled() && $this->_helperData->getApplyDiscount()
            !== 'no') {
            if ($account->getStatus() != 1) {
                $this->_checkoutSession->unsAffiliateCode();
                $this->_helperData->deleteAffiliateCode();
                return $this;
            }
            $applyDiscountType = $this->_helperData->getApplyDiscount();
            $discountValue = $this->_helperData->getDiscountValue();
            $baseDiscount = $this->_helperData->calculateAffiliate($total->getBaseSubtotal(), $discountValue,
                $applyDiscountType);
            if ($baseDiscount <= 0) {
                return $this;
            }
            $discount = $this->_priceCurrency->convert($baseDiscount);
            $total->addTotalAmount($this->getCode(), -$discount);
            $total->setAffiliateDiscount($discount);
            $total->setBaseAffiliateDiscount($baseDiscount);
            return $this;
        }
        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        $title = __('Affiliate Discount');
        if ($this->_checkoutSession->getAffiliateCode()) {
            $title = __('Affiliate Discount (%1)', $this->_checkoutSession->getAffiliateCode());
        }
        return [
            'code' => $this->getCode(),
            'title' => $title,
            'value' => $total->getAffiliateDiscount()
        ];
    }
}



