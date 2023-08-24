<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class MyGiftCard extends Action
{
    protected PageFactory $resultPageFactory;
    protected GiftCardHelper $giftCardHelper;
    protected CurrentCustomer $currentCustomer;

    public function __construct(
        Context         $context,
        GiftCardHelper  $giftCardHelper,
        PageFactory     $resultPageFactory,
        CurrentCustomer $currentCustomer
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->giftCardHelper = $giftCardHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You must login to view this page !'));
            return $this->_redirect('customer/account/login');
        }

        if (!$this->giftCardHelper->isGiftCardEnabled()) {
            $this->messageManager->addErrorMessage(__('Gift Card is disabled !'));
            return $this->_redirect('customer/account/index/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Gift Card'));
        return $resultPage;
    }
}