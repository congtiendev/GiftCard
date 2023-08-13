<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class BuyGiftCard implements ObserverInterface
{
    protected $giftCardHelper;
    protected $giftCardFactory;
    protected $productRepository;
    protected $giftCardHistoryFactory;

    public function __construct(
        GiftCardHelper         $giftCardHelper,
        GiftCardFactory        $giftCardFactory,
        ProductRepository      $productRepository,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        $this->giftCardHelper = $giftCardHelper;
        $this->giftCardFactory = $giftCardFactory;
        $this->productRepository = $productRepository;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $virtualOrderItems = $this->getVirtualOrderItems($order);

        if (!empty($virtualOrderItems)) {
            $this->createGiftCards($virtualOrderItems, $order);
        }
    }

    protected function getVirtualOrderItems(Order $order): array
    {
        $virtualOrderItems = [];

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === 'virtual') {
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                $giftCardAmount = $product->getCustomAttribute('giftcard_amount');

                if ($giftCardAmount) {
                    $item->setData('giftcard_amount', $giftCardAmount->getValue());
                    $virtualOrderItems[] = $item;
                }
            }
        }

        return $virtualOrderItems;
    }

    protected function createGiftCards(array $items, Order $order): void
    {
        $customerId = $order->getCustomerId();
        $codeLength = $this->giftCardHelper->getCodeLength();

        foreach ($items as $item) {
            $qty = $item->getQtyOrdered();

            for ($i = 0; $i < $qty; $i++) {
                $giftCode = $this->giftCardHelper->generateGiftCode($codeLength);

                $giftCardCode = $this->giftCardFactory->create();
                $giftCardData = [
                    'code' => $giftCode,
                    'amount_used' => 0,
                    'balance' => $item->getData('giftcard_amount'),
                    'created_from' => $order->getIncrementId(),
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                try {
                    $giftCardCode->addData($giftCardData)->save();
                } catch (\Exception $e) {
                    $this->giftCardHelper->debug('Error when creating gift card code: ' . $e->getMessage());
                }

                $giftCardHistory = $this->giftCardHistoryFactory->create();
                $giftCardHistoryData = [
                    'giftcard_id' => $giftCardCode->getId(),
                    'customer_id' => $customerId,
                    'amount' => $item->getData('giftcard_amount'),
                    'action' => 'Create',
                    'action_time' => date('Y-m-d H:i:s'),
                ];

                try {
                    $giftCardHistory->addData($giftCardHistoryData)->save();
                } catch (\Exception $e) {
                    $this->giftCardHelper->debug('Error when creating gift card history: ' . $e->getMessage());
                }
            }
        }
    }
}
