<?php

namespace Mageplaza\Affiliate\Controller\Refer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;


class Index extends Action
{
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected CustomerFactory $customerFactory;
    protected CustomerSession $customerSession;
    protected $resultRedirect;

    public function __construct(
        Context         $context,
        HelperData      $helperData,
        AccountFactory  $accountFactory,
        CustomerFactory $customerFactory,
        CustomerSession $customerSession,
        ResultFactory   $resultFactory
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        if (!$this->helperData->isLogin()) {
            $this->messageManager->addErrorMessage(__('You must login to view this page !'));
            return $this->resultRedirect->setPath('customer/account/login');
        }

        if (!$this->helperData->isAffiliateEnabled()) {
            $this->messageManager->addErrorMessage(__('Affiliate is disabled !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }

        $code = $this->getRequest()->getParam($this->helperData->getUrlKey());
        if (!$code) {
            $this->messageManager->addErrorMessage(__('Refer link is not valid !'));
            return $this->resultRedirect->setPath('customer/account/index/');
        }

        $account = $this->accountFactory->create()->load($code, 'code');
        $referencedBy = $this->customerFactory->create()->load($account->getCustomerId());
        $referenceByName = $referencedBy->getFirstname() . ' ' . $referencedBy->getLastname();

        // Kiểm tra xem cookie có tồn tại code hay không
        if (isset($_COOKIE[$this->helperData->getUrlKey()])) {
            $this->messageManager->addNoticeMessage(__('You have already been referred by %1', $referenceByName));
            return $this->resultRedirect->setPath('customer/account/index/');
        }
        setcookie($this->helperData->getUrlKey(), $code, time() + (86400 * 365), "/");
        $this->messageManager->addSuccessMessage(__('You are referred by %1', $referenceByName));
        return $this->resultRedirect->setPath($this->_redirect->getRefererUrl());
    }
}