<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

class Save extends Action
{
    protected AccountFactory $_accountFactory;

    public function __construct(Context $context, AccountFactory $accountFactory)
    {
        parent::__construct($context);
        $this->_accountFactory = $accountFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        dd($data);
    }
}