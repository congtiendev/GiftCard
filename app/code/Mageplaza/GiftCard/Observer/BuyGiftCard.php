<?php

namespace Mageplaza\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelperData;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Mageplaza\GiftCard\Helper\SendEmail as GiftCardSendEmail;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;

class BuyGiftCard implements ObserverInterface
{
    protected MessageManager $messageManager;
    protected GiftCardFactory $giftCardFactory;
    protected CheckoutSession $checkoutSession;
    protected ProductRepository $productRepository;
    protected GiftCardHelperData $giftCardHelperData;
    protected GiftCardSendEmail $giftCardSendEmail;
    protected PriceHelper $priceCurrency;
    protected GiftCardHistoryFactory $giftCardHistoryFactory;

    public function __construct(
        MessageManager         $messageManager,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        ProductRepository      $productRepository,
        GiftCardHelperData     $giftCardHelperData,
        GiftCardSendEmail      $giftCardSendEmail,
        PriceHelper            $priceCurrency,
        GiftCardHistoryFactory $giftCardHistoryFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->giftCardFactory = $giftCardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->giftCardHelperData = $giftCardHelperData;
        $this->giftCardSendEmail = $giftCardSendEmail;
        $this->priceCurrency = $priceCurrency;
        $this->giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function execute(Observer $observer)
    {
        if (!$this->giftCardHelperData->isGiftCardEnabled()) {
            return $this;
        }
        $order = $observer->getOrder();
        $codeLength = $this->giftCardHelperData->getCodeLength();
        foreach ($order->getAllItems() as $item) {
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);
            $giftCardAmount = $product->getCustomAttribute('giftcard_amount');
            // check product is virtual and has gift card amount
            if ($item->getProductType() === 'virtual' && isset($giftCardAmount) && $giftCardAmount->getValue() > 0) {
                for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                    $giftCard = $this->giftCardFactory->create();
                    $giftCardHistory = $this->giftCardHistoryFactory->create();
                    $code = $this->giftCardHelperData->generateGiftCode($codeLength);
                    try {
                        // create new gift card
                        $giftCard->addData([
                            'code' => $code,
                            'amount_used' => 0,
                            'balance' => $giftCardAmount->getValue(),
                            'created_from' => $order->getIncrementId(),
                        ])->save();

                        // add history when buy gift card success
                        $giftCardHistory->addData([
                            'giftcard_id' => $giftCard->getId(),
                            'customer_id' => $order->getCustomerId(),
                            'amount' => $giftCardAmount->getValue(),
                            'action' => 'Create',
                        ]);
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Buy Gift Card Error ! Please try again.'));
                    }
                    // save history when buy gift card success
                    $this->addHistoryCreate($giftCardHistory);
                    $emailInfo = [
                        'mail_to' => $order->getCustomerEmail(),
                        'customer_name' => $order->getCustomerName(),
                        'increment_id' => $order->getIncrementId(),
                        'gift_card_code' => $giftCard->getCode(),
                        'balance' => $this->priceCurrency->currency($giftCard->getBalance(), true, false),
                    ];
                    // send email to customer
                    $this->giftCardSendEmail->sendEmail($emailInfo, 1);
                }
            }
        }
        // add history when use gift card for order
        if ($this->checkoutSession->getCode()) {
            $this->addHistoryUse($order);
        }
    }

    public function addHistoryCreate($giftCardHistory): void
    {
        try {
            $giftCardHistory->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Insert History Error ! Please try again.'));
        }
    }

    public function addHistoryUse($order): void
    {
        $giftCard = $this->giftCardFactory->create()->load($this->checkoutSession->getCode(), 'code');
        if (!$giftCard->getId()) {
            return;
        }
        $amount = $this->calculateAmount($giftCard->getBalance(), $giftCard->getAmountUsed(), $order->getBaseSubtotal
        ());
        // update gift card amount used
        $this->setAmountUsed($giftCard, $amount['amount_used']);
        // if user is logged in, save gift card history
        if ($order->getCustomerId()) {
            $giftCardHistory = $this->giftCardHistoryFactory->create()->load($order->getCustomerId(), 'customer_id');
            try {
                $giftCardHistory->setData([
                    'giftcard_id' => $giftCard->getId(),
                    'customer_id' => $order->getCustomerId(),
                    'amount' => '- ' . $amount['amount'],
                    'action' => 'Use for Order #' . $order->getIncrementId()
                ])->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Use Gift Card Error ! Please try again.'));
            }
        }
        // Unset applied gift card
        $this->checkoutSession->unsCode();
    }

    public function setAmountUsed($giftCard, $amountUsed): void
    {
        try {
            $giftCard->setAmountUsed($amountUsed)->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Use Gift Card Error ! Please try again.'));
        }
    }

    public function calculateAmount($balance, $amountUsed, $baseSubtotal): array
    {
        $amountUse = min($balance - $amountUsed, $baseSubtotal);
        $amountUsed += $amountUse;
        return [
            'amount' => $amountUse,
            'amount_used' => $amountUsed
        ];
    }
}