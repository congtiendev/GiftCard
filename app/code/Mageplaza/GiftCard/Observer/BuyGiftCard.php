<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelperData;
use Mageplaza\GiftCard\Helper\SendEmail as GiftCardHelperEmail;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class BuyGiftCard implements ObserverInterface
{
    protected MessageManager $messageManager;
    protected GiftCardFactory $giftCardFactory;
    protected ProductRepository $productRepository;
    protected GiftCardHelperData $giftCardHelperData;
    protected GiftCardHelperEmail $giftCardHelperEmail;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;

    public function __construct(
        MessageManager         $messageManager,
        GiftCardFactory        $giftCardFactory,
        ProductRepository      $productRepository,
        GiftCardHelperData     $giftCardHelperData,
        GiftCardHelperEmail    $giftCardHelperEmail,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->giftCardFactory = $giftCardFactory;
        $this->productRepository = $productRepository;
        $this->giftCardHelperData = $giftCardHelperData;
        $this->giftCardHelperEmail = $giftCardHelperEmail;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute(Observer $observer)
    {
        // Send email
        $this->giftCardHelperEmail->sendEmail();
        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        $codeLength = $this->giftCardHelperData->getCodeLength();

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === 'virtual') {
                // Get product by id
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                $giftCardAmount = $product->getCustomAttribute('giftcard_amount');

                // Check gift card amount value
                if (isset($giftCardAmount) && $giftCardAmount->getValue() > 0) {
                    for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                        $giftCardCode = $this->giftCardFactory->create();
                        $giftCardHistory = $this->giftCardHistoryFactory->create();
                        // Random gift code
                        $giftCode = $this->giftCardHelperData->generateGiftCode($codeLength);

                        $giftCardData = [
                            'code' => $giftCode,
                            'amount_used' => 0,
                            'balance' => $giftCardAmount->getValue(),
                            'created_from' => $order->getIncrementId(),
                        ];

                        try {
                            // Save gift card code
                            $giftCardCode->addData($giftCardData)->save();
                            $giftCardHistoryData = [
                                'giftcard_id' => $giftCardCode->getId(),
                                'customer_id' => $customerId,
                                'amount' => $giftCardAmount->getValue(),
                                'action' => 'Create',
                            ];
                            // Add gift card history data
                            $giftCardHistory->addData($giftCardHistoryData);
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__('Buy Gift Card Error ! Please try again.'));
                        }

                        try {
                            $giftCardHistory->save();
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__('Insert History Error ! Please try again.'));
                        }
                    }
                }
            }
        }
    }
}
