<?php

namespace Mageplaza\Affiliate\Controller\History;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;


class Index extends Action
{
    protected $resultRedirect;
    protected HelperData $helperData;
    protected PageFactory $resultPageFactory;
    protected CurrentCustomer $currentCustomer;

    public function __construct(
        Context         $context,
        HelperData      $helperData,
        PageFactory     $resultPageFactory,
        CurrentCustomer $currentCustomer,
        ResultFactory   $resultFactory
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->helperData->isLogin()) {
            return $this->resultRedirect->setPath('customer/account/login');
        }

        if (!$this->helperData->isAffiliateEnabled()) {
            $this->messageManager->addErrorMessage(__('Affiliate is disabled !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Affiliate'));
        return $resultPage;
    }
}