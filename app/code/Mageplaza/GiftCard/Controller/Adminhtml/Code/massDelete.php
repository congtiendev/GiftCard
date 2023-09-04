<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory;


class MassDelete extends Action
{
    protected CollectionFactory $collectionFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_massdelete';

    public function __construct(
        Context           $context,
        CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collectionSize = $collection->getSize();
        try {
            foreach ($collection as $giftCard) {
                $giftCard->delete();
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
