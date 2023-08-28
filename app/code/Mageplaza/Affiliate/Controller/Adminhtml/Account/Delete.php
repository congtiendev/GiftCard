<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    protected $resultRedirect;
    protected $accountFactory;

    public function __construct(
        Context        $context,
        AccountFactory $accountFactory,
        ResultFactory  $resultFactory
    )
    {
        parent::__construct($context);
        $this->accountFactory = $accountFactory;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $account = $this->accountFactory->create()->load($id);
        if ($id && !$account->getId()) {
            $this->messageManager->addErrorMessage(__('This account no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $account->delete();
            $this->messageManager->addSuccessMessage(__('The account has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirect->setPath('*/*/');
    }
}