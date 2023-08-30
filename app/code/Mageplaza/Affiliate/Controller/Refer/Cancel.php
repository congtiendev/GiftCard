<?php

namespace Mageplaza\Affiliate\Controller\Refer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;

class Cancel extends Action
{
    protected HelperData $helperData;
    protected $resultRedirect;

    public function __construct(
        Context       $context,
        HelperData    $helperData,
        ResultFactory $resultFactory
    )
    {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function execute()
    {
        if (!$this->helperData->isLogin()) {
            return $this->resultRedirect->setPath('customer/account/login');
        }
        if ($this->helperData->getAffiliateCode()) {
            $this->helperData->deleteAffiliateCode();
            $this->messageManager->addSuccessMessage(__('You have canceled the affiliate account !'));
            return $this->resultRedirect->setPath('affiliate/history/index');
        }
        $this->messageManager->addErrorMessage(__('You have not been referred by any users yet !'));
        return $this->resultRedirect->setPath('customer/account/index/');
    }
}