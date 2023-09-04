<?php

namespace Mageplaza\GiftCard\Block\Customer\MyGiftCard;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;
use Mageplaza\GiftCard\Controller\Customer\GetGiftCardHistory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;


class ListGift extends Template
{
    protected Session $customerSession;
    protected GiftCardHelper $giftCardHelper;
    protected GetGiftCardHistory $getGiftCardHistory;
    protected CollectionFactory $giftCardHistoryCollectionFactory;
    protected $_template = 'Mageplaza_GiftCard::customer/mygiftcard/list.phtml';


    public function __construct(
        CollectionFactory  $giftCardHistoryCollectionFactory,
        GetGiftCardHistory $getGiftCardHistory,
        Session            $customerSession,
        GiftCardHelper     $giftCardHelper,
        Context            $context,
        array              $data = []
    )
    {
        $this->giftCardHistoryCollectionFactory = $giftCardHistoryCollectionFactory;
        $this->getGiftCardHistory = $getGiftCardHistory;
        $this->customerSession = $customerSession;
        $this->giftCardHelper = $giftCardHelper;
        parent::__construct($context, $data);
    }


    public function isEnable(): bool
    {
        return $this->giftCardHelper->isGiftCardEnabled();
    }

    public function allowRedeemGiftCard(): bool
    {
        return $this->giftCardHelper->allowRedeemGiftCard();
    }

    public function _prepareLayout(): ListGift
    {
        return parent::_prepareLayout();
    }
}