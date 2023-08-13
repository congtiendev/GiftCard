<?php

namespace Mageplaza\GiftCard\Block\Customer\MyGiftCard;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;
use Mageplaza\GiftCard\Model\ResourceModel\CustomerEntity\CollectionFactory as CustomerEntityCollectionFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class ListGift extends Template
{
    protected GiftCardHelper $giftCardHelper;
    protected CurrentCustomer $currentCustomer;
    protected CollectionFactory $_giftcardFactory;
    protected CustomerEntityCollectionFactory $_customerEntityCollectionFactory;


    public function __construct(
        Context                         $context,
        CurrentCustomer                 $currentCustomer,
        CollectionFactory               $giftcardFactory,
        CustomerEntityCollectionFactory $customerEntityCollectionFactory,
        GiftCardHelper                  $giftCardHelper,
        array                           $data = []
    )
    {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->_giftcardFactory = $giftcardFactory;
        $this->_customerEntityCollectionFactory = $customerEntityCollectionFactory;
        $this->giftCardHelper = $giftCardHelper;
        $this->getGiftCardHistory();
        $this->getBalance();
    }

    public function getGiftCardHistory()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $giftCardCollection = $this->_giftcardFactory->create();
        $giftCardCollection->getSelect()->joinLeft(
            ['giftcard_code' => $giftCardCollection->getTable('giftcard_code')],
            'main_table.giftcard_id = giftcard_code.giftcard_id',
            ['code']
        );
        $giftCardCollection->addFieldToFilter('customer_id', $customerId);
        return $giftCardCollection;
    }

    public function getBalance()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $customerEntityCollection = $this->_customerEntityCollectionFactory->create();
        $customerEntityCollection->addFieldToFilter('entity_id', $customerId);
        return $customerEntityCollection->getFirstItem()->getGiftcardBalance();
    }

    public function isEnable(): bool
    {
        return $this->giftCardHelper->isGiftCardEnabled();
    }

    public function formatDateTime($date)
    {
        return $this->giftCardHelper->formatDate($date);
    }


    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }


}