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
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_new';
    protected $_authorization;

    public function __construct(ForwardFactory $resultForwardFactory, Context $context, AuthorizationInterface $_authorization)
    {
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_authorization = $_authorization;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultForwardFactory->create();
        $resultPage->forward('edit');
        return $resultPage;
    }
}

