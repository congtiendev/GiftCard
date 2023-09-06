<?php

namespace Mageplaza\Affiliate\Controller\History;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Model\AccountFactory;


class Index extends Action
{
    protected $resultRedirect;
    protected HelperData $helperData;
    protected PageFactory $resultPageFactory;
    protected AccountFactory $accountFactory;
    protected CurrentCustomer $currentCustomer;

    public function __construct(
        Context         $context,
        HelperData      $helperData,
        PageFactory     $resultPageFactory,
        AccountFactory  $accountFactory,
        CurrentCustomer $currentCustomer,
        ResultFactory   $resultFactory
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountFactory = $accountFactory;
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

        if ($this->helperData->getAffiliateCode()) {
            $account = $this->accountFactory->create()->load($this->helperData->getAffiliateCode(), 'code');
            if ($account->getId() && $account->getStatus() != 1) {
                $this->helperData->deleteAffiliateCode();
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Affiliate'));
        return $resultPage;
    }
}