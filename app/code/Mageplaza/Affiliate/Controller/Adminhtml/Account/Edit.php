<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_edit';
    protected PageFactory $_resultPageFactory;
    protected AccountFactory $_accountFactory;
    protected Registry $_coreRegistry;

    public function __construct(
        Context        $context,
        Registry       $coreRegistry,
        AccountFactory $accountFactory,
        PageFactory    $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_accountFactory = $accountFactory;
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $account = $this->_accountFactory->create()->load($id);
        if ($id && !$account->getId()) {
            $this->messageManager->addErrorMessage(__('This account no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $this->_coreRegistry->register('affiliate_account', $account);
        return $this->_resultPageFactory->create();
    }

}