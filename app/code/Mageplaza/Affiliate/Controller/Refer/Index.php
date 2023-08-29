<?php

namespace Mageplaza\Affiliate\Controller\Refer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;


class Index extends Action
{
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected $resultRedirect;

    public function __construct(
        Context        $context,
        HelperData     $helperData,
        AccountFactory $accountFactory,
        ResultFactory  $resultFactory
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
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
        $referenceByName = $account->getAccountName($account->getId());

        if ($this->helperData->getAffiliateCode()) {
            $this->messageManager->addNoticeMessage(__('You have already been referred by %1', $referenceByName));
            return $this->resultRedirect->setPath('affiliate/history/index');
        }

        $this->helperData->setAffiliateCode($code);
        $this->messageManager->addSuccessMessage(__('You are referred by %1', $referenceByName));
        return $this->resultRedirect->setPath('affiliate/history/index');
    }
}