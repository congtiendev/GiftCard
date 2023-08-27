<?php

namespace Mageplaza\Affiliate\Controller\Adminhtml\Account;

use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Exception;

class Save extends Action
{
    protected $messageManager;
    protected HelperData $helperData;
    protected AccountFactory $accountFactory;
    protected CustomerFactory $customerFactory;
    protected HistoryFactory $historyFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_Affiliate::account_save';

    public function __construct(
        Context          $context,
        AccountFactory   $accountFactory,
        CustomerFactory  $customerFactory,
        HistoryFactory   $historyFactory,
        HelperData       $helperData,
        ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->accountFactory = $accountFactory;
        $this->customerFactory = $customerFactory;
        $this->historyFactory = $historyFactory;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $this->messageManager->getMessages(true);
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('*/*/');
        }

        $data = $this->getRequest()->getPostValue();
        $accountId = $data['account_id'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $account = $this->accountFactory->create();
        if (!$accountId && !$account->load($accountId)->getId()) {
            $data['code'] = $this->helperData->generateCode();
        }
        $currentBalance = $account->load($accountId)->getBalance() ?? 0;

        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            if (!$customer->getId()) {
                $this->messageManager->addErrorMessage(__('Customer does not exist.'));
                return $this->_redirect('*/*/new');
            }
            if ($account->load($customerId, 'customer_id')->getId()) {
                $this->messageManager->addWarningMessage(__('Customer already has an affiliate account.'));
                return $this->_redirect('*/*/new');
            }
        }

        try {
            $account->addData($data)->save();
            if ((!$accountId && $data['balance'] > 0) || ($accountId && $data['balance'] !== $currentBalance)) {
                $this->updateHistory($account, $data);
            }

            $this->messageManager->addSuccessMessage(__('Affiliate Account has been saved.'));
            $this->messageManager->getMessages(true);
            return $this->_redirect('*/*/index');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Affiliate Account could not be saved.'));
            return $this->_redirect('*/*/new');
        }
    }

    public function updateHistory($account, $data): void
    {
        try {
            $history = $this->historyFactory->create();
            $history->addData([
                'order_id' => NULL,
                'order_increment_id' => NULL,
                'customer_id' => $account->getCustomerId(),
                'amount' => $data['balance'],
                'is_admin_change' => 1,
                'status' => $data['status']
            ])->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Affiliate history could not be saved.'));
            return;
        }
    }
}
