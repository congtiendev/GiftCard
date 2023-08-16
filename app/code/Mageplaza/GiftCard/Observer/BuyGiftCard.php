<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class BuyGiftCard implements ObserverInterface
{
    protected GiftCardHelper $giftCardHelper;
    protected MessageManager $messageManager;
    protected GiftCardFactory $giftCardFactory;
    protected ProductRepository $productRepository;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;

    public function __construct(
        MessageManager         $messageManager,
        GiftCardHelper         $giftCardHelper,
        GiftCardFactory        $giftCardFactory,
        ProductRepository      $productRepository,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        $this->giftCardHelper = $giftCardHelper;
        $this->messageManager = $messageManager;
        $this->giftCardFactory = $giftCardFactory;
        $this->productRepository = $productRepository;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        $codeLength = $this->giftCardHelper->getCodeLength();

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
                        $giftCode = $this->giftCardHelper->generateGiftCode($codeLength);

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
