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
        $code = $_COOKIE[$this->_helperData->getUrlKey()] ?? null;
        $account = $this->_accountFactory->create()->load($code, 'code');
        if ($account->getId() && $this->_helperData->isAffiliateEnabled()) {
            $applyDiscountType = $this->_helperData->getApplyDiscount();
            $discountValue = $this->_helperData->getDiscountValue();
            if ($applyDiscountType === 'fixed') {
                $baseDiscount = $discountValue;
            } else if ($applyDiscountType === 'percentage') {
                $baseDiscount = $quote->getSubtotal() * $discountValue / 100;
            } else {
                $baseDiscount = 0;
            }
            $baseDiscount = $this->_priceHelper->currency(min($baseDiscount, $quote->getSubtotal()), false, false);
            $total->setDiscountAmount($baseDiscount);
            $total->setGrandTotal($total->getGrandTotal() - $baseDiscount);
            $quote->setAffiliateDiscount($baseDiscount);
        }
        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        $totals = [];
        $amount = $quote->getAffiliateDiscount();
        if ($amount > 0) {
            $totals[] = [
                'code' => $this->getCode(),
                'title' => __('Affiliate Discount'),
                'value' => $amount
            ];
        }
        return $totals;
    }

}