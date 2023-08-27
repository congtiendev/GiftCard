<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class Edit extends Action
{
    protected PageFactory $_resultPageFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_edit';

    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        return $this->_resultPageFactory->create();
    }

}