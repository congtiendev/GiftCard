<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;

class Edit extends Action
{

    protected Registry $_coreRegistry;
    protected PageFactory $_resultPageFactory;
    protected GiftCardFactory $_giftCardFactory;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_edit';

    public function __construct(
        PageFactory     $resultPageFactory,
        GiftCardFactory $_giftCardFactory,
        Registry        $_coreRegistry,
        Context         $context)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_giftCardFactory = $_giftCardFactory;
        $this->_coreRegistry = $_coreRegistry;
        parent::__construct($context);
    }


    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $giftCard = $this->_giftCardFactory->create()->load($id);
        if ($id && !$giftCard->getId()) {
            $this->messageManager->addErrorMessage(__('This gift card no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $this->_coreRegistry->register('giftcard', $giftCard);
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Gift Card Update')));
        return $resultPage;
    }
}
