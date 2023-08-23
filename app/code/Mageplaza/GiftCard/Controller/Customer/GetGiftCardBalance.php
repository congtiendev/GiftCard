<?php

namespace Mageplaza\GiftCard\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class GetGiftCardBalance extends Action
{
    protected Session $customerSession;
    protected JsonFactory $jsonFactory;

    public function __construct(
        Context     $context,
        JsonFactory $jsonFactory,
        Session     $customerSession
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $jsonData = [
            'giftCardBalance' => $this->customerSession->getCustomer()->getGiftcardBalance(),
            'message' => 'Get balance successfully'
        ];

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($jsonData);
        return $resultJson;
    }
}

