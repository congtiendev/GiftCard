<?php

namespace Mageplaza\GiftCard\Controller\Test;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;

class Update extends Action
{
    protected $_giftCardFactory;

    public function __construct(
        Context         $context,
        GiftCardFactory $giftCardFactory
    )
    {
        $this->_giftCardFactory = $giftCardFactory;
        parent::__construct($context);
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $giftCardId = $this->getRequest()->getParam('giftcard_id');
        $giftCard = $this->_giftCardFactory->create()->load($giftCardId);
        $data = [
            'code' => substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10),
            'balance' => random_int(100, 1000),
            'amount_used' => 0,
            'created_from' => random_int(0, 1) ? 'admin' : random_int(100000000, 999999999),
        ];
        if ($giftCard->getId()) {
            try {
                $giftCard->addData($data)->save();
                return $this->_redirect('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Gift Card could not be updated.'));
            }
        }
    }
}