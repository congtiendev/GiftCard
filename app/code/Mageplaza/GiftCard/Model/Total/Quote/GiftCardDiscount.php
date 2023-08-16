<?php


namespace Mageplaza\GiftCard\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class GiftCardDiscount extends AbstractTotal
{
    protected PriceCurrencyInterface $_priceCurrency;
    protected CheckoutSession $_checkoutSession;
    protected GiftCardFactory $_giftCardFactory;
    protected PriceHelper $_priceHelper;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CheckoutSession        $checkoutSession,
        GiftCardFactory        $giftCardFactory,
        PriceHelper            $priceHelper
    )
    {
        $this->_priceHelper = $priceHelper;
        $this->_priceCurrency = $priceCurrency;
        $this->_checkoutSession = $checkoutSession;
        $this->_giftCardFactory = $giftCardFactory;
    }

    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    ): GiftCardDiscount
    {
        parent::collect($quote, $shippingAssignment, $total);
        $giftCard = $this->_giftCardFactory->create()->load($this->_checkoutSession->getCode(), 'code');
        $baseDiscount = $this->_checkoutSession->getAmount() ?? 0;
        $_subTotal = $total->getSubtotal();

        if ($baseDiscount > $_subTotal) {
            // Update amount_used
//            $giftCard->setAmountUsed($giftCard->getAmountUsed() + $_subTotal);
            $baseDiscount = $_subTotal;
        }
        // Chuyển đổi sang tiền tệ của store
        $discount = $this->_priceCurrency->convert($baseDiscount);

        // Cập nhật lại giá trị cho total
        $total->addTotalAmount('giftcard_discount', -$discount);

        // Cập nhật lại giá trị cho base total
        $total->addBaseTotalAmount('giftcard_discount', -$baseDiscount);

        // Cập nhật lại giá trị cho quote (tổng tiền của giỏ hàng)
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);

        // Cập nhật lại giá trị cho quote (tổng tiền của giỏ hàng)
        $quote->setCustomDiscount(-$discount);

        return $this;
    }

    public function fetch(Quote $quote, Total $total)
    {
        $amount = $total->getCustomDiscount();
        $title = __('Gift Card');
        return [
            'code' => 'giftcard_discount',
            'title' => $title,
            'value' => $amount
        ];
    }

}