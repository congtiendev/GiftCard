<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action\Context;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $collectionFactory;
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

        $ids = $this->getRequest()->getParam('selected');
        //dd($ids);

        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select items to delete.'));
        } else {
            try {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('giftcard_id', ['in' => $ids]);
                foreach ($collection as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', count($ids)));
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
