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
        $account = $this->accountFactory->create();
        $history = $this->historyCollectionFactory->create()->getHistoryByCustomer($customerId);
        $staticBlockId = $this->helperData->getRegisterStaticBlock();
        $staticBlock = $this->blockFactory->create()->load($staticBlockId)->getContent();

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
        $referencedBy = '';
        if ($this->helperData->getAffiliateCode()) {
            $account->load($this->helperData->getAffiliateCode(), 'code');
            $referencedBy = $account->getAccountName($account->getId());
        }
        $account->load($customerId, 'customer_id');
        return $this->resultJsonFactory->create()->setData([
            'referenced_by' => $referencedBy,
            'account' => $account->getId(),
            'static_block' => $staticBlock,
            'balance' => $this->helperData->priceFormat($account->getBalance()),
            'refer_link' => $this->helperData->getReferLink($account->getCode()),
            'history' => $historyData
        ]);
    }


}