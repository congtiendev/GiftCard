<?php

namespace Mageplaza\GiftCard\Block;

use Magento\Framework\View\Element\Template;

class Coupon extends Template
{
    public function __construct(
        Template\Context $context,
        array            $data = []
    )
    {
        parent::__construct($context, $data);
        $this->setTemplate('Mageplaza_GiftCard::cart/coupon.phtml');
    }
}