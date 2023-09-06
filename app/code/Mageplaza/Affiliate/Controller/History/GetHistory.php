<?php

namespace Mageplaza\Affiliate\Controller\History;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CurrentCustomer;
use Mageplaza\Affiliate\Model\ResourceModel\History\CollectionFactory;

class GetHistory extends Action
{
    protected $resultRedirect;
    protected HelperData $helperData;
    protected JsonFactory $resultJsonFactory;
    protected AccountFactory $accountFactory;
    protected CurrentCustomer $currentCustomer;
    protected CollectionFactory $historyCollectionFactory;

    public function __construct(
        Context           $context,
        HelperData        $helperData,
        JsonFactory       $resultJsonFactory,
        CurrentCustomer   $currentCustomer,
        CollectionFactory $historyCollectionFactory,
        AccountFactory    $accountFactory,
        ResultFactory     $resultFactory
    )
    {
        $this->helperData = $helperData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->currentCustomer = $currentCustomer;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->accountFactory = $accountFactory;
        parent::__construct($context);
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        if (!$this->helperData->isLogin()) {
            return $this->resultRedirect->setPath('customer/account/login');
        }

        $customerId = $this->currentCustomer->getCustomerId();
        $history = $this->historyCollectionFactory->create()->getHistoryByCustomer($customerId);

        $historyData = [];
        foreach ($history->getData() as $item) {
            $historyData[] = [
                'order_increment_id' => $item['order_increment_id'],
                'title' => $item['is_admin_change'] ? 'Changed by admin' : 'Created from order #' . $item['order_id'],
                'amount' => $this->helperData->priceFormat($item['amount']),
                'status' => $item['status'],
                'created_at' => $this->helperData->formatDateTime($item['created_at'])
            ];
        }
        $account = $this->accountFactory->create();
        $referencedBy = '';
        if ($this->helperData->getAffiliateCode()) {
            $referAccount = $account->load($this->helperData->getAffiliateCode(), 'code');
            $referencedBy = $referAccount->getAccountName($referAccount->getId());
        }
        $customerAccount = $account->load($customerId, 'customer_id');
        return $this->resultJsonFactory->create()->setData([
            'referenced_by' => $referencedBy ?? null,
            'account' => $customerId === $customerAccount->getCustomerId(),
            'account_status' => $customerAccount->getStatus(),
            'balance' => $this->helperData->priceFormat($customerAccount->getBalance()),
            'refer_link' => $this->helperData->getReferLink($customerAccount->getCode()),
            'refer_code' => $customerAccount->getCode(),
            'history' => $historyData
        ]);
    }
}