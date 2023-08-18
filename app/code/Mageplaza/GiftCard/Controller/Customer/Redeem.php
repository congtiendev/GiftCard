<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\CustomerEntityFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;


class Redeem extends Action
{
    protected Context $context;
    protected Session $customerSession;
    protected GiftCardFactory $giftCardFactory;
    protected CustomerFactory $customerFactory;
    protected CustomerEntityFactory $customerEntityFactory;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;


    public function __construct(
        Context                $context,
        Session                $customerSession,
        GiftCardFactory        $giftCardFactory,
        CustomerFactory        $customerFactory,
        CustomerEntityFactory  $customerEntityFactory,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->giftCardFactory = $giftCardFactory;
        $this->customerFactory = $customerFactory;
        $this->customerEntityFactory = $customerEntityFactory;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute()
    {
        $code = trim($this->getRequest()->getPostValue('code')) ?? null;
        if (!$code) {
            $this->messageManager->addErrorMessage(__('Please enter your gift card code !'));
            $this->_redirect('giftcard/customer/mygiftcard');
            return;
        }
        $customerSession = $this->customerSession->getCustomer();
        $giftCard = $this->giftCardFactory->create()->load($code, 'code');

        $data = [
            'giftcard_id' => $giftCard->getId(),
            'customer_id' => $customerSession->getId(),
            'amount' => '-' . ($giftCard->getBalance() - $giftCard->getAmountUsed()),
            'action' => 'Redeem',
        ];

        $amount = $giftCard->getBalance() - $giftCard->getAmountUsed();
        if (!$giftCard->getId() || $amount <= 0) {
            $this->messageManager->addErrorMessage($giftCard->getId() ? __('Gift Card has expired !') : __('Gift Card does not exist !'));
        } else {
            $currentBalance = $this->customerSession->getCustomer()->getGiftcardBalance();

            $this->setBalance($customerSession->getId(), $currentBalance, $amount);
            $this->setAmountUsed($giftCard->getId(), $giftCard->getBalance());
            $this->saveHistory($data);
        }
        $this->_redirect('giftcard/customer/mygiftcard');
    }


    public function saveHistory($history)
    {
        try {
            $this->giftCardHistoryFactory->create()->addData($history)->save();
            $this->messageManager->addSuccessMessage(__('Successfully used gift card !'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error when using gift card !'));
        }
    }

    public function setBalance($customerId, $currentBalance, $amount)
    {
        $customerEntityFactory = $this->customerEntityFactory->create()->load($customerId, 'entity_id');
        $customerEntityFactory->setGiftcardBalance($currentBalance + $amount);
        try {
            $customerEntityFactory->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error when redeem gift card !'));
        }
    }

    public function setAmountUsed($giftCardId, $balance): void
    {
        $giftCard = $this->giftCardFactory->create()->load($giftCardId);
        if (!$giftCard->getId()) {
            $this->messageManager->addErrorMessage(__('Gift Card does not exist !'));
            return;
        }
        $giftCard->setAmountUsed($balance);
        try {
            $giftCard->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error when redeem gift card !'));
        }
    }
}
