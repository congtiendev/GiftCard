<?php

namespace Mageplaza\GiftCard\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class GiftCardDiscount extends AbstractTotal
{
    protected PriceCurrencyInterface $_priceCurrency;
    protected CheckoutSession $_checkoutSession;
    protected GiftCardFactory $_giftCardFactory;
    protected PriceHelper $_priceHelper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        PriceHelper            $priceHelper
    )
    {
        $this->_priceHelper = $priceHelper;
        $this->_priceCurrency = $priceCurrency;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftCardFactory = $giftCardFactory;
    }


    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total): GiftCardDiscount
    {
        parent::collect($quote, $shippingAssignment, $total);
        // Load gift card using the code stored in checkout session
        $giftCard = $this->_giftCardFactory->create()->load($this->_checkoutSession->getCode(), 'code');
        if (!$giftCard->getId()) {
            return $this;
        }
        // Calculate discount amount
        $baseDiscount = $this->_checkoutSession->getAmount() ?? 0;

        if ($baseDiscount > $total->getSubtotal()) {
            $baseDiscount = $total->getSubtotal();
        }
        $discount = $this->_priceCurrency->convert($baseDiscount);

        // Update total amounts
        $total->addTotalAmount('giftcard_discount', -$discount);
        $total->addBaseTotalAmount('giftcard_discount', -$baseDiscount);
//        dd($total->getBaseGrandTotal());
//        $total->setBaseGrandTotal($total->getBaseGrandTotal() -$baseDiscount);
        $quote->setGiftCardDiscount(-$discount);
        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        $totals = [];
        $amount = $this->_checkoutSession->getAmount() ?? 0;
        if ($amount > $total->getSubtotal()) {
            $amount = $total->getSubtotal();
        }
        if ($amount > 0) {
            $totals[] = [
                'code' => 'giftcard_discount',
                'title' => __('Gift Card (%1)', $this->_checkoutSession->getCode()),
                'value' => $amount
            ];
        }
        return $totals;
    }


}
