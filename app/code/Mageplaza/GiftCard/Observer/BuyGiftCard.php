<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelperData;
use Mageplaza\GiftCard\Helper\SendEmail as GiftCardHelperEmail;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class BuyGiftCard implements ObserverInterface
{
    protected MessageManager $messageManager;
    protected GiftCardFactory $giftCardFactory;
    protected CheckoutSession $checkoutSession;
    protected ProductRepository $productRepository;
    protected GiftCardHelperData $giftCardHelperData;
    protected GiftCardHelperEmail $giftCardHelperEmail;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;

    public function __construct(
        MessageManager         $messageManager,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        ProductRepository      $productRepository,
        GiftCardHelperData     $giftCardHelperData,
        GiftCardHelperEmail    $giftCardHelperEmail,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->giftCardFactory = $giftCardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->giftCardHelperData = $giftCardHelperData;
        $this->giftCardHelperEmail = $giftCardHelperEmail;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        $codeLength = $this->giftCardHelperData->getCodeLength();

        foreach ($order->getAllItems() as $item) {
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);
            $giftCardAmount = $product->getCustomAttribute('giftcard_amount');
            if ($item->getProductType() === 'virtual' && isset($giftCardAmount) && $giftCardAmount->getValue() > 0) {

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
        if ($this->checkoutSession->getCode()) {
            $this->updateHistory($order);
        }
    }

    public function updateHistory($order): void
    {
        $giftCard = $this->giftCardFactory->create()->load($this->checkoutSession->getCode(), 'code');
        if (!$giftCard->getId()) {
            return;
        }
        
        $amount = 0;
        $amountUse = $giftCard->getBalance() - $giftCard->getAmountUsed();
        if ($amountUse > $order->getSubtotal()) {
            $amountUsed = $order->getSubtotal() + $giftCard->getAmountUsed();
            $amount = $order->getSubtotal();
        } else {
            $amountUsed = $giftCard->getBalance();
            $amount = $order->getSubtotal() - $giftCard->getAmountUsed();
        }

        try {
            $giftCard->setAmountUsed($amountUsed)->save();
            // if user is logged in, save gift card history
            if ($order->getCustomerId()) {
                $giftCardHistory = $this->giftCardHistoryFactory->create();
                $giftCardHistory->load($order->getCustomerId(), 'customer_id');
                $data = [
                    'giftcard_id' => $giftCard->getId(),
                    'customer_id' => $order->getCustomerId(),
                    'amount' => '- ' . $amount,
                    'action' => 'Use for Order #' . $order->getIncrementId()
                ];
                $giftCardHistory->setData($data);
                $giftCardHistory->save();
            }
            // Unset applied gift card
            $this->checkoutSession->unsCode();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Use Gift Card Error ! Please try again.'));
        }
    }
}
