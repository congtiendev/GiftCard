<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Edit extends Action
{

    protected PageFactory $_resultPageFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_edit';

    public function __construct(PageFactory $resultPageFactory, Context $context)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Gift Card Update')));
        return $resultPage;
    }
}
