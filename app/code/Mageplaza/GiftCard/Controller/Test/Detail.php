<?php

namespace Mageplaza\GiftCard\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;


class Detail extends Action
{
    protected $_giftCardFactory;

    public function __construct(GiftCardFactory $giftCardFactory, Context $context)
    {
        $this->_giftCardFactory = $giftCardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $giftCardId = $this->getRequest()->getParam('giftcard_id');
        $giftCard = $this->_giftCardFactory->create()->load($giftCardId);
        if ($giftCard->getId()) {
            echo "<ul style='list-style-type: none;'>
                    <li>Gift Card ID: " . $giftCard->getId() . "</li>
                    <li>Gift Card Code: " . $giftCard->getCode() . "</li>
                    <li>Gift Card Balance: " . $giftCard->getBalance() . "</li>
                    <li>Gift Card Amount Used: " . $giftCard->getAmountUsed() . "</li>
                    <li>Gift Card Created From: " . $giftCard->getCreatedFrom() . "</li>
                </ul>";
        } else {
            $this->messageManager->addError(__('Gift Card does not exist.'));
            return $this->_redirect('*/*/index');
        }
    }
}