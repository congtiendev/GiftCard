<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    protected GiftCardFactory $giftCardFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_delete';

    public function __construct(Context $context, GiftCardFactory $giftCardFactory)
    {
        parent::__construct($context);
        $this->giftCardFactory = $giftCardFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $giftCard = $this->giftCardFactory->create()->load($id);
        if (!$giftCard->getId()) {
            $this->messageManager->addErrorMessage(__('This gift card no longer exists.'));
        } else {
            try {
                $giftCard->delete();
                $this->messageManager->addSuccessMessage(__('The gift card has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('The gift card could not be deleted.'));
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }


}