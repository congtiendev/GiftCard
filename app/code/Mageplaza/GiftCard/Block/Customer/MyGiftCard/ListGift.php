<?php

namespace Mageplaza\GiftCard\Block\Customer\MyGiftCard;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\GiftCard\Controller\Customer\GetGiftCardHistory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;


class ListGift extends Template
{
    protected CurrencyModel $currencyModel;
    protected Session $customerSession;
    protected TimezoneInterface $dateTime;
    protected GiftCardHelper $giftCardHelper;
    protected PriceCurrencyInterface $priceCurrency;
    protected GetGiftCardHistory $getGiftCardHistory;
    protected CollectionFactory $giftCardHistoryCollectionFactory;
    protected $_template = 'Mageplaza_GiftCard::customer/mygiftcard/list.phtml';


    public function __construct(
        CollectionFactory  $giftCardHistoryCollectionFactory,
        GetGiftCardHistory $getGiftCardHistory,
        Session            $customerSession,
        GiftCardHelper     $giftCardHelper,
        CurrencyModel      $currencyModel,
        TimezoneInterface  $dateTime,
        Context            $context,
        array              $data = []
    )
    {
        $this->giftCardHistoryCollectionFactory = $giftCardHistoryCollectionFactory;
        $this->getGiftCardHistory = $getGiftCardHistory;
        $this->customerSession = $customerSession;
        $this->giftCardHelper = $giftCardHelper;
        $this->currencyModel = $currencyModel;
        parent::__construct($context, $data);
        $this->dateTime = $dateTime;
        $this->getGiftCardHistory();
        $this->getBalance();
    }

    public function getGiftCardHistory(): \Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\Collection
    {
//        $customerId = $this->customerSession->getCustomer()->getId();
//        $giftCardListHistory = $this->getGiftCardHistory->execute($customerId);
//        return $giftCardListHistory;

        $customerId = $this->customerSession->getCustomer()->getId();
        $giftCardListHistory = $this->giftCardHistoryCollectionFactory->create();
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

    /**
     * @throws \Exception
     */
    public function formatDateTime($date): string
    {
        return $this->giftCardHelper->formatDateTime($date);
    }

    /**
     * Get currency symbol for current locale and currency code
     *
     * @return string
     */
    public function getCurrentCurrencySymbol(): string
    {
        return $this->currencyModel->getCurrencySymbol();
    }


    public function _prepareLayout(): ListGift
    {
        return parent::_prepareLayout();
    }
}