<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;


class NewAction extends Action
{
    protected $_resultForwardFactory = false;

    protected $_authorization;

    public function __construct(ForwardFactory $resultForwardFactory, Context $context, AuthorizationInterface $_authorization)
    {
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_authorization = $_authorization;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_isAllowed()) {
            $this->messageManager->addErrorMessage(__('You do not have permission to access this page.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $resultPage = $this->_resultForwardFactory->create();
        $resultPage->forward('edit');
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageplaza_GiftCard::giftcard_new_code');
    }
}

