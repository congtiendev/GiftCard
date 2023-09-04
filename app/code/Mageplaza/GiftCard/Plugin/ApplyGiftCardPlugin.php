<?php


namespace Mageplaza\GiftCard\Plugin;

use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Block\Cart\Coupon as CouponBlock;

class ApplyGiftCardPlugin
{
    protected CheckoutSession $checkoutSession;
    protected GiftCardFactory $giftcardFactory;

    public function __construct(
        CheckoutSession $checkoutSession,
        GiftCardFactory $giftCardFactory)
    {
        $this->giftcardFactory = $giftCardFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetCouponCode(CouponBlock $subject, $default)
    {
        $code = $this->checkoutSession->getCode();
        $giftCardCode = $this->giftcardFactory->create()->load($code, 'code')->getCode();
        return $giftCardCode ?: $default;
    }
}