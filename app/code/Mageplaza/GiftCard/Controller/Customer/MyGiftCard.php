<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;

class MyGiftCard extends Action
{
    protected PageFactory $resultPageFactory;
    protected CurrentCustomer $currentCustomer;

    public function __construct(
        Context         $context,
        PageFactory     $resultPageFactory,
        CurrentCustomer $currentCustomer
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You must login to view this page !'));
            return $this->_redirect('customer/account/login');
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Gift Card'));
        return $resultPage;
    }
}