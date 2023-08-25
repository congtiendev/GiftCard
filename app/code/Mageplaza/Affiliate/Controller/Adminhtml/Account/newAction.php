<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class newAction extends Action
{
    protected $_resultForwardFactory = false;

    public function __construct(
        Context        $context,
        ForwardFactory $resultForwardFactory
    )
    {
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultForwardFactory->create();
        $resultPage->forward('edit');
        return $resultPage;
    }

}