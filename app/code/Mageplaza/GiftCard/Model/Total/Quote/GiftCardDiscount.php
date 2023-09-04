<?php

namespace Mageplaza\GiftCard\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class GiftCardDiscount extends AbstractTotal
{
    protected GiftCardHelper $_giftCardHelper;
    protected CheckoutSession $_checkoutSession;
    protected GiftCardFactory $_giftCardFactory;
    protected PriceCurrencyInterface $_priceCurrency;

    public function __construct(
        GiftCardHelper         $giftCardHelper,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        PriceCurrencyInterface $priceCurrency
    )
    {
        $this->_giftCardHelper = $giftCardHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftCardFactory = $giftCardFactory;
        $this->_priceCurrency = $priceCurrency;
    }


    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total): GiftCardDiscount
    {
        parent::collect($quote, $shippingAssignment, $total);
        $giftCard = $this->_giftCardHelper->getGiftCardCode($this->_checkoutSession->getCode());
        if (!$giftCard->getId() || !$this->_giftCardHelper->isGiftCardEnabled() ||
            !$this->_giftCardHelper->allowUsedGiftCardAtCheckout()) {
            return $this;
        }
        $baseDiscount = $this->calculateDiscount($giftCard->getBalance(), $giftCard->getAmountUsed(), $total->getBaseSubtotal());
        if ($baseDiscount <= 0) {
            return $this;
        }
        $discount = $this->_priceCurrency->convert($baseDiscount);
        $total->addTotalAmount($this->getCode(), -$discount);
        $total->setGiftcardDiscount($discount);
        $total->setBaseGiftcardDiscount($baseDiscount);
        return $this;
    }

    public function fetch(Quote $quote, Total $total): array
    {
        return [
            'code' => $this->getCode(),
            'title' => __('Gift Card (%1)', $this->_checkoutSession->getCode()),
            'value' => $total->getGiftcardDiscount()
        ];
    }

    public function calculateDiscount($balance, $amountUsed, $baseSubtotal)
    {
        return min($balance - $amountUsed, $baseSubtotal);
    }
}