<?php

namespace Mageplaza\GiftCard\Block\Customer\MyGiftCard;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;


class ListGift extends Template
{
    protected Session $customerSession;
    protected TimezoneInterface $dateTime;
    protected GiftCardHelper $giftCardHelper;
    protected CollectionFactory $giftcardFactory;
    protected PriceCurrencyInterface $priceCurrency;


    public function __construct(
        CollectionFactory      $giftcardFactory,
        GiftCardHelper         $giftCardHelper,
        PriceCurrencyInterface $priceCurrency,
        Session                $customerSession,
        TimezoneInterface      $dateTime,
        Context                $context,
        array                  $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->giftcardFactory = $giftcardFactory;
        $this->giftCardHelper = $giftCardHelper;
        parent::__construct($context, $data);
        $this->dateTime = $dateTime;
        $this->getGiftCardHistory();
        $this->getBalance();
    }

    public function getGiftCardHistory(): \Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\Collection
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $giftCardListHistory = $this->giftcardFactory->create();
        $giftCardListHistory->getListHistory($customerId);
        return $giftCardListHistory;
    }

    public function getBalance()
    {
        return $this->customerSession->getCustomer()->getGiftcardBalance();
    }

    public function isEnable(): bool
    {
        return $this->giftCardHelper->isGiftCardEnabled();
    }

    public function allowRedeemGiftCard(): bool
    {
        return $this->giftCardHelper->allowRedeemGiftCard();
    }

    public function formatDateTime($date)
    {
        return $this->dateTime->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM, // Định dạng ngày
            \IntlDateFormatter::MEDIUM, // Định dạng thời gian
            null,
            null,
            'd/M/y' // Định dạng ngày/tháng/năm
        );
    }


    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }


}