<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory as GiftCardCollection;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory\CollectionFactory as GiftCardHistoryCollection;
use Mageplaza\GiftCard\Model\ResourceModel\CustomerEntity\CollectionFactory as CustomerEntityCollectionFactory;

class Redeem extends Action
{
    protected CurrentCustomer $currentCustomer;
    protected GiftCardCollection $giftCardCollection;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;
    protected GiftCardHistoryCollection $giftCardHistoryCollection;
    protected CustomerEntityCollectionFactory $customerEntityCollectionFactory;

    public function __construct(
        Context                         $context,
        CurrentCustomer                 $currentCustomer,
        GiftCardCollection              $giftCardCollection,
        GiftCardHistoryFactory          $giftCardHistoryFactory,
        GiftCardHistoryCollection       $giftCardHistoryCollection,
        CustomerEntityCollectionFactory $customerEntityCollectionFactory
    )
    {
        parent::__construct($context);
        $this->currentCustomer = $currentCustomer;
        $this->giftCardCollection = $giftCardCollection;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
        $this->giftCardHistoryCollection = $giftCardHistoryCollection;
        $this->customerEntityCollectionFactory = $customerEntityCollectionFactory;
    }

    public function execute()
    {
        $code = trim($this->getRequest()->getPostValue('code'));
        $giftCard = $this->giftCardCollection->create()->addFieldToFilter('code', $code)->getFirstItem();
        $giftCardId = $giftCard->getId();
        $customerId = $this->currentCustomer->getCustomerId();
        $customerEntity = $this->customerEntityCollectionFactory->create()->addFieldToFilter('entity_id', $customerId)->getFirstItem();

        $giftCardHistory = $this->giftCardHistoryCollection->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('giftcard_id', $giftCardId)
            ->addFieldToFilter('action', 'Redeem')
            ->getFirstItem();


        if (!$giftCard->getId() || $giftCard->getBalance() === $giftCard->getAmountUsed()) {
            $this->messageManager->addErrorMessage($giftCard->getId() ? __('Gift Card has expired !') : __('Gift Card does not exist !'));
        } elseif ($giftCardHistory->getData()) {
            $this->messageManager->addErrorMessage(__('Gift Card has been used !'));
        } else {
            $customerGiftCardBalance = $customerEntity->getGiftcardBalance();
            $newGiftCardBalance = $customerGiftCardBalance + $giftCard->getBalance();

            $customerEntity->setGiftcardBalance($newGiftCardBalance)->save();

            $this->saveHistory([
                'giftcard_id' => $giftCard->getId(),
                'customer_id' => $customerId,
                'amount' => 0,
                'action' => 'Redeem',
                'action_time' => date('Y-m-d H:i:s')
            ]);
        }
        $this->_redirect('giftcard/customer/mygiftcard');
    }

    public function saveHistory($history)
    {
        $this->giftCardHistoryFactory->create()->addData($history)->save();
        $this->messageManager->addSuccessMessage(__('Successfully used gift card !'));
    }
}
