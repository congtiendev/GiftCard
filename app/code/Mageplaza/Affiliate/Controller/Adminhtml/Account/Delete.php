<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class Delete extends Action
{
    protected AccountFactory $accountFactory;

    public function __construct(
        Context        $context,
        AccountFactory $accountFactory
    )
    {
        parent::__construct($context);
        $this->accountFactory = $accountFactory;
    }

    public function execute()
    {
        $ids = $this->getRequest()->getPost('selected', []);
        $id = $this->getRequest()->getParam('id');

        if (!$id && empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select items to delete.'));
        } else {
            try {
                $account = $this->accountFactory->create();
                if ($id) {
                    $account->load($id)->delete();
                    $this->messageManager->addSuccessMessage(__('Account has been deleted.'));
                } else {
                    foreach ($ids as $accountId) {
                        $account->load($accountId)->delete();
                    }
                    $this->messageManager->addSuccessMessage(__('Selected accounts have been deleted.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while deleting accounts.'));
            }
        }
        $this->_redirect('*/*/index');
    }
}
