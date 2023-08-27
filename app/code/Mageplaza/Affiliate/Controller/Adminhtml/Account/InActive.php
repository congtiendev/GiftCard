<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class InActive extends Action
{
    protected AccountFactory $_accountFactory;

    public function __construct(
        Context        $context,
        AccountFactory $accountFactory
    )
    {
        $this->_accountFactory = $accountFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $ids = $data['selected'] ?? null;
        if ($ids) {
            try {
                $account = $this->_accountFactory->create();
                foreach ($ids as $accountId) {
                    $account->load($accountId)->setStatus(0)->save();
                }
                $this->messageManager->addSuccessMessage(__('Selected accounts have been inactivated.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while inactive accounts.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select account(s).'));
        }
        $this->_redirect('*/*/index');
    }
}

?>