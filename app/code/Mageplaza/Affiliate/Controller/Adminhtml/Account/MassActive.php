<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Model\ResourceModel\Account\CollectionFactory as AccountCollectionFactory;

class MassActive extends Action
{
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_massactive';

    protected Filter $filter;
    protected $resultRedirect;
    protected AccountCollectionFactory $accountCollectionFactory;

    public function __construct(
        Filter                   $filter,
        Context                  $context,
        AccountCollectionFactory $accountCollectionFactory,
        ResultFactory            $resultFactory
    )

    {
        $this->filter = $filter;
        parent::__construct($context);
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->accountCollectionFactory->create());
        $collectionSize = $collection->getSize();
        try {
            foreach ($collection as $account) {
                $account->setStatus(1);
                $account->save();
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been activated.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirect->setPath('*/*/index');
    }
}

?>