<?php

namespace Mageplaza\GiftCard\Block;

use Magento\Framework\View\Element\Template;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class Config extends Template
{
    protected GiftCardHelper $giftCardHelper;

    public function __construct(Template\Context $context, GiftCardHelper $giftCardHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->giftCardHelper = $giftCardHelper;
    }

    public function isGiftCardEnabled(): bool
    {
        return $this->giftCardHelper->isGiftCardEnabled();
    }

    public function allowUsedGiftCardAtCheckout(): bool
    {
        return $this->giftCardHelper->allowUsedGiftCardAtCheckout();
    }

    public function allowRedeemGiftCard(): bool
    {
        return $this->giftCardHelper->allowRedeemGiftCard();
    }
}