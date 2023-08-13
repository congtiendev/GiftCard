<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\Code;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class Save extends Action
{
    protected $giftCardFactory;
    protected $giftCardHelper;

    public function __construct(Context $context, GiftCardFactory $giftCardFactory, GiftCardHelper $giftCardHelper)
    {
        parent::__construct($context);
        $this->giftCardFactory = $giftCardFactory;
        $this->giftCardHelper = $giftCardHelper;
    }


    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $giftCardId = $data['giftcard_id'] ?? null;
        $giftCard = $this->giftCardFactory->create();
        $giftCard->load($giftCardId);

        if (!$giftCard->getId()) {
            $data['code'] = $this->giftCardHelper->generateGiftCode($data['code_length']);
        }

        try {
            $giftCard->addData($data)->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Gift Card could not be saved.'));
        }


        return $this->_redirect('*/*/index');
    }
}
