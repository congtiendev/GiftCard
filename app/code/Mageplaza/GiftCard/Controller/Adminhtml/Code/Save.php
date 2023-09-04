<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;


class Save extends Action
{
    protected $resultRedirect;
    protected GiftCardFactory $giftCardFactory;
    protected GiftCardHelper $giftCardHelper;
    public const ADMIN_RESOURCE = 'Mageplaza_GiftCard::giftcard_save';

    public function __construct(
        Context         $context,
        GiftCardFactory $giftCardFactory,
        GiftCardHelper  $giftCardHelper,
        ResultFactory   $resultFactory
    )
    {
        parent::__construct($context);
        $this->giftCardFactory = $giftCardFactory;
        $this->giftCardHelper = $giftCardHelper;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }


    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $giftCardId = $this->getRequest()->getParam('giftcard_id');
        $giftCard = $this->giftCardFactory->create()->load($giftCardId);
        // Create
        if (!$giftCard->getId()) {
            $data['code'] = $this->giftCardHelper->generateGiftCode($data['code_length']);
        }
        try {
            $giftCard->addData($data)->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Gift Card could not be saved.'));
        }
        if ($this->getRequest()->getParam('back')) {
            return $this->resultRedirect->setPath('*/*/edit', ['id' => $giftCard->getId(), '_current' => true]);
        }
        return $this->resultRedirect->setPath('*/*/');
    }
}
