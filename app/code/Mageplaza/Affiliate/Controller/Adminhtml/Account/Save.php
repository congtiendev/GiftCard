<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Exception;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_save';
    protected $resultRedirect;
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected CustomerFactory $customerFactory;
    protected HistoryFactory $historyFactory;

    public function __construct(
        Context         $context,
        AccountFactory  $accountFactory,
        CustomerFactory $customerFactory,
        HistoryFactory  $historyFactory,
        ResultFactory   $resultFactory,
        HelperData      $helperData
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
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
        if ($accountId && $account->getId()) {
            // Edit
            $currentBalance = $account->getBalance();
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
                'status' => $this->getRequest()->getParam('status'),
                'balance' => $this->getRequest()->getParam('balance'),
                'code' => $this->helperData->generateCode()
            ];
        }

        try {
            $account->setData($data)->save();
            if ((!$accountId && $balance > 0) || ($accountId && $balance !== $currentBalance)) {
                $this->updateHistory($customerId, $balance, $status);
            }
            $this->messageManager->addSuccessMessage(__('Affiliate Account has been saved successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Affiliate Account could not be saved.'));
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
