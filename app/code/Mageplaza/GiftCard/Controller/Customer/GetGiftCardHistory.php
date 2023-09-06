<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CurrentCustomer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface as DateTimeFormatter;


class GetGiftCardHistory extends Action
{
    protected CollectionFactory $_giftCardHistoryCollectionFactory;
    protected DateTimeFormatter $_dateTimeFormatter;
    protected CurrentCustomer $_currentCustomer;
    protected TimezoneInterface $_timezone;
    protected JsonFactory $_jsonFactory;
    protected Data $_priceHelper;


    public function __construct(
        Context           $context,
        TimezoneInterface $timezone,
        JsonFactory       $jsonFactory,
        Data              $priceHelper,
        CurrentCustomer   $currentCustomer,
        DateTimeFormatter $dateTimeFormatter,
        CollectionFactory $giftCardHistoryCollectionFactory
    )
    {
        parent::__construct($context);
        $this->_timezone = $timezone;
        $this->_priceHelper = $priceHelper;
        $this->_jsonFactory = $jsonFactory;
        $this->_currentCustomer = $currentCustomer;
        $this->_dateTimeFormatter = $dateTimeFormatter;
        $this->_giftCardHistoryCollectionFactory = $giftCardHistoryCollectionFactory;
    }

    public function execute()
    {
        $customerId = $this->_currentCustomer->getCustomerId();
        $giftCardListHistory = $this->_giftCardHistoryCollectionFactory->create();
        $giftCardBalance = $this->_currentCustomer->getCustomer()->getGiftcardBalance();
        $giftCardListHistory->getListHistory($customerId);

        $historyData = [];
        foreach ($giftCardListHistory->getData() as $item) {
            $historyData[] = [
                'action_time' => $this->_timezone->date($item['action_time'])->format('n/j/y'),
                'code' => $item['code'],
                'amount' => $this->_priceHelper->currency($item['amount']),
                'action' => $item['action'],
            ];
        }
        return $this->_jsonFactory->create()->setData([
            'giftCardHistory' => $historyData,
            'giftCardBalance' => $this->_priceHelper->currency($giftCardBalance)
        ]);
    }
}