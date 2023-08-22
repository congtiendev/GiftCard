<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;

class GetGiftCardHistory extends Action
{
    protected CollectionFactory $_giftCardHistoryCollectionFactory;
    protected CurrentCustomer $_currentCustomer;
    protected JsonFactory $_jsonFactory;

    public function __construct(
        Context           $context,
        JsonFactory       $jsonFactory,
        CurrentCustomer   $currentCustomer,
        CollectionFactory $giftCardHistoryCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_jsonFactory = $jsonFactory;
        $this->_currentCustomer = $currentCustomer;
        $this->_giftCardHistoryCollectionFactory = $giftCardHistoryCollectionFactory;
    }

    public function execute()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        $giftCardListHistory = $this->_giftCardHistoryCollectionFactory->create();
        $giftCardListHistory->getListHistory($customerId);

        $jsonData = [
            'giftCardHistory' => $giftCardListHistory->getData(),
            'message' => 'Data retrieved successfully'
        ];

        $resultJson = $this->_jsonFactory->create();
        $resultJson->setData($jsonData);
        return $resultJson;
    }
}