<?php

namespace Mageplaza\Affiliate\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\Quote\Address\Total;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class AffiliateDiscount extends AbstractTotal
{
    protected HelperData $_helperData;
    protected PriceHelper $_priceHelper;
    protected OrderFactory $_orderFactory;
    protected AccountFactory $_accountFactory;
    protected CheckoutSession $_checkoutSession;

    public function __construct(
        HelperData      $helperData,
        PriceHelper     $priceHelper,
        OrderFactory    $orderFactory,
        AccountFactory  $accountFactory,
        CheckoutSession $checkoutSession
    )
    {
        $this->_helperData = $helperData;
        $this->_priceHelper = $priceHelper;
        $this->_orderFactory = $orderFactory;
        $this->_accountFactory = $accountFactory;
        $this->_checkoutSession = $checkoutSession;
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
            !== 'No') {

            $applyDiscountType = $this->_helperData->getApplyDiscount();
            $discountValue = $this->_helperData->getDiscountValue();
            $subtotal = $total->getSubtotal();
            $baseDiscount = $this->_helperData->calculateAffiliate($subtotal, $discountValue, $applyDiscountType);
            $discount = $this->_priceHelper->currency($baseDiscount, false, false);

            $total->addTotalAmount($this->getCode(), -$discount);
            $total->addBaseTotalAmount($this->getCode(), -$baseDiscount);

            $total->setSubtotalWithDiscount($total->getSubtotal() + $discount);
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $baseDiscount);

            $total->setAffiliateDiscount($baseDiscount);
            $total->setBaseAffiliateDiscount($baseDiscount);
            return $this;
        }
        return $this;
    }


    public function fetch(Quote $quote, Total $total): array
    {
        $totals = [];
        $amount = $total->getAffiliateDiscount();
        if ($amount > 0) {
            $totals[] = [
                'code' => $this->getCode(),
                'title' => __('Affiliate Discount'),
                'value' => $this->_priceHelper->currency($amount, false, false)
            ];
        }
        return $totals;
    }
}



