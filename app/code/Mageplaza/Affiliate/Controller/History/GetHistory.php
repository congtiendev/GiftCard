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
use Magento\Cms\Model\BlockFactory;

class GetHistory extends Action
{
    protected $resultRedirect;
    protected HelperData $helperData;
    protected JsonFactory $resultJsonFactory;
    protected AccountFactory $accountFactory;
    protected CurrentCustomer $currentCustomer;
    protected CollectionFactory $historyCollectionFactory;
    protected BlockFactory $blockFactory;

    public function __construct(
        Context           $context,
        HelperData        $helperData,
        JsonFactory       $resultJsonFactory,
        CurrentCustomer   $currentCustomer,
        CollectionFactory $historyCollectionFactory,
        AccountFactory    $accountFactory,
        BlockFactory      $blockFactory,
        ResultFactory     $resultFactory
    )
    {
        $this->helperData = $helperData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->currentCustomer = $currentCustomer;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->accountFactory = $accountFactory;
        $this->blockFactory = $blockFactory;
        parent::__construct($context);
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
        $customerId = $this->currentCustomer->getCustomerId();
        $account = $this->accountFactory->create()->load($customerId, 'customer_id');
        $history = $this->historyCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
        $staticBlockId = $this->helperData->getRegisterStaticBlock();
        $staticBlock = $this->blockFactory->create()->load($staticBlockId)->getContent();

        $historyData = [];
        foreach ($history->getData() as $item) {
            $historyData[] = [
                'order_id' => $item['order_id'],
                'order_increment_id' => $item['order_increment_id'],
                'title' => $item['is_admin_change'] ? 'Changed by admin' : 'Created from order #' . $item['order_id'],
                'amount' => $this->helperData->priceFormat($item['amount']),
                'status' => $item['status'] ? 'Active' : 'Inactive',
                'created_at' => $this->helperData->formatDateTime($item['created_at'])
            ];
        }

        return $this->resultJsonFactory->create()->setData([
            'account' => $account->getId(),
            'static_block' => $staticBlock,
            'balance' => $this->helperData->priceFormat($account->getBalance()),
            'refer_link' => $this->helperData->getReferLink($account->getCode()),
            'history' => $historyData
        ]);
    }


}