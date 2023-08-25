<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_index';

    public function __construct(
        \Magento\Backend\App\Action\Context        $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Gift Card Code')));

        return $resultPage;
    }

}