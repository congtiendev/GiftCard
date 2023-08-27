<?php

namespace Mageplaza\Affiliate\Controller\History;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\Affiliate\Helper\Data as HelperData;

class Index extends Action
{
    protected HelperData $helperData;
    protected PageFactory $resultPageFactory;
    protected CurrentCustomer $currentCustomer;

    public function __construct(
        Context         $context,
        HelperData      $helperData,
        PageFactory     $resultPageFactory,
        CurrentCustomer $currentCustomer
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You must login to view this page !'));
            return $this->_redirect('customer/account/login');
        }

        if (!$this->helperData->isAffiliateEnabled()) {
            $this->messageManager->addErrorMessage(__('Affiliate is disabled !'));
            return $this->_redirect('customer/account/index/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Affiliate'));
        return $resultPage;
    }
}