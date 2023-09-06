<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Customer\Model\CustomerFactory;
use Mageplaza\Affiliate\Helper\SendEmail;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Exception;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_save';
    protected $resultRedirect;
    protected HelperData $helperData;
    protected SendEmail $sendEmail;
    protected AccountFactory $accountFactory;
    protected HistoryFactory $historyFactory;
    protected CustomerFactory $customerFactory;
    protected PriceHelper $priceCurrency;

    public function __construct(
        Context         $context,
        PriceHelper     $priceCurrency,
        AccountFactory  $accountFactory,
        CustomerFactory $customerFactory,
        HistoryFactory  $historyFactory,
        ResultFactory   $resultFactory,
        HelperData      $helperData,
        SendEmail       $sendEmail
    )
    {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
        $this->helperData = $helperData;
        $this->sendEmail = $sendEmail;
        $this->accountFactory = $accountFactory;
        $this->customerFactory = $customerFactory;
        $this->historyFactory = $historyFactory;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $balance = $this->getRequest()->getParam('balance');
        $status = $this->getRequest()->getParam('status');

        $account = $this->accountFactory->create()->load($accountId);
        $customer = $this->customerFactory->create()->load($customerId);
        $initialBalance = null;
        $initialStatus = null;
        if ($accountId && $account->getId()) {
            // Edit
            $initialBalance = $account->getBalance();
            $initialStatus = $account->getStatus();
            $data = [
                'account_id' => $account->getId(),
                'status' => $status,
                'balance' => $balance,
            ];
        } else {
            // Create
            if (!$customer->getId() || $account->isExitsCustomer($customerId)) {
                $this->messageManager->addErrorMessage(__((!$customer->getId() ? 'Customer does not exist.' : 'Customer already has an affiliate account.')));
                return $this->resultRedirect->setPath('*/*/new');
            }
            $data = [
                'customer_id' => $customerId,
                'status' => $status,
                'balance' => $balance,
                'code' => $this->helperData->generateCode()
            ];
        }

        try {
            $account->setData($data)->save();
            if (!$accountId) {
                $this->sendEmail->sendEmail([
                    'mail_to' => $customer->getEmail(),
                    'customer_name' => $customer->getName(),
                    'code' => $data['code'],
                    'refer_link' => $this->helperData->getReferLink($data['code']),
                    'balance' => $this->priceCurrency->currency($balance, true, false),
                    'date' => date('Y-m-d H:i:s'),
                ], 1);
            }
            if ((!$accountId && $balance > 0) || ($accountId && $balance !== $initialBalance)) {
                $this->updateHistory($customerId, $balance, $status);
                $this->sendEmail->sendEmail([
                    'mail_to' => $customer->getEmail(),
                    'customer_name' => $customer->getName(),
                    'initial_balance' => $this->priceCurrency->currency($initialBalance, true, false),
                    'new_balance' => $this->priceCurrency->currency($balance, true, false),
                    'date' => date('Y-m-d H:i:s'),
                ], 2);
            }
            if ($accountId && $status !== $initialStatus) {
                $this->updateHistory($customerId, $balance, $status);
                $this->sendEmail->sendEmail([
                    'mail_to' => $customer->getEmail(),
                    'customer_name' => $customer->getName(),
                    'current_status' => $status ? 'Active' : 'Inactive',
                    'date' => date('Y-m-d H:i:s'),
                ], 4);
            }
            $this->messageManager->addSuccessMessage(__('Affiliate Account has been saved successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        if ($this->getRequest()->getParam('back')) {
            return $this->resultRedirect->setPath('*/*/edit', ['id' => $account->getId(), '_current' => true]);
        }
        return $this->resultRedirect->setPath('*/*/');
    }

    public function updateHistory($customerId, $balance, $status): void
    {
        try {
            $history = $this->historyFactory->create();
            $history->addData([
                'order_id' => NULL,
                'order_increment_id' => NULL,
                'customer_id' => $customerId,
                'amount' => $balance,
                'is_admin_change' => 1,
                'status' => $status
            ])->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Affiliate history could not be saved.'));
            return;
        }
    }
}
