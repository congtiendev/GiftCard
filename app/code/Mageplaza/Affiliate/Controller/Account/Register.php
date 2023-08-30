<?php

namespace Mageplaza\Affiliate\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;

class Register extends Action
{
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected CustomerSession $customerSession;
    protected $resultRedirect;

    public function __construct(
        Context         $context,
        HelperData      $helperData,
        AccountFactory  $accountFactory,
        CustomerSession $customerSession,
        ResultFactory   $resultFactory
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
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

        $customerId = $this->customerSession->getCustomerId();
        $account = $this->accountFactory->create()->load($customerId, 'customer_id');
        if ($account->getId()) {
            $this->messageManager->addErrorMessage(__('You already have an affiliate account !'));
            return $this->resultRedirect->setPath('affiliate/history/index');
        }
        return $this->createAccount($account, $customerId);
    }

    public function createAccount($account, $customerId)
    {
        try {
            $account->setData([
                'customer_id' => $customerId,
                'code' => $this->helperData->generateCode(),
                'balance' => 0,
                'status' => 1
            ])->save();
            $this->messageManager->addSuccessMessage(__('Your affiliate account has been created successfully !'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while processing your data. Please try again later.'));
        } finally {
            return $this->resultRedirect->setPath('affiliate/history/index');
        }
    }
}

