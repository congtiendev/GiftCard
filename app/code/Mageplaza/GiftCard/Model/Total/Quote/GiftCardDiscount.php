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
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class GiftCardDiscount extends AbstractTotal
{
    protected PriceCurrencyInterface $_priceCurrency;
    protected GiftCardHelper $_giftCardHelper;
    protected CheckoutSession $_checkoutSession;
    protected GiftCardFactory $_giftCardFactory;
    protected PriceHelper $_priceHelper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        GiftCardHelper         $giftCardHelper,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        PriceHelper            $priceHelper
    )
    {
        $this->_priceHelper = $priceHelper;
        $this->_giftCardHelper = $giftCardHelper;
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
        // Load gift card
        $giftCard = $this->_giftCardHelper->getGiftCardCode($this->_checkoutSession->getCode());
        if (!$giftCard->getId() || !$this->_giftCardHelper->isGiftCardEnabled() ||
            !$this->_giftCardHelper->allowUsedGiftCardAtCheckout()) {
            return $this;
        }

        // Calculate discount amount
        $baseDiscount = $giftCard->getBalance() - $giftCard->getAmountUsed();
        if ($baseDiscount <= 0) {
            return $this;
        }
        if ($baseDiscount > $total->getSubtotal()) {
            $baseDiscount = $total->getSubtotal();
        }

        $discount = $this->_priceCurrency->convert($baseDiscount);
        // Update total amounts
        $total->addTotalAmount($giftCard->getCode(), -$discount);
        $total->addBaseTotalAmount($giftCard->getCode(), -$baseDiscount);
        $total->setSubtotalWithDiscount($total->getSubtotal() + $discount);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discount);

        $quote->setGiftcardDiscount($discount);
        $quote->setBaseGiftcardDiscount($baseDiscount);
        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        $totals = [];
        $amount = $quote->getGiftcardDiscount();
        if ($amount > 0) {
            $totals[] = [
                'code' => $this->getCode(),
                'title' => __('Gift Card (%1)', $this->_checkoutSession->getCode()),
                'value' => $amount
            ];
        }
        return $totals;
    }
}
