<?php

namespace Mageplaza\GiftCard\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;

class Delete extends Action
{
    protected $_giftCardFactory;

    public function __construct(Context $context, GiftCardFactory $giftCardFactory
    )
    {
        $this->_giftCardFactory = $giftCardFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $giftCardId = $this->getRequest()->getParam('giftcard_id');
        $giftCard = $this->_giftCardFactory->create()->load($giftCardId);
        if ($giftCard->getId()) {
            try {
                $giftCard->delete();
                $this->messageManager->addSuccess(__('Gift Card has been deleted.'));
                return $this->_redirect('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Gift Card could not be deleted.'));
                return $this->_redirect('*/*/index');
            }
        }
    }
}