<?php

namespace Mageplaza\GiftCard\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageplaza\GiftCard\Model\GiftCardHistoryFactory;
use Mageplaza\GiftCard\Helper\Data as HelperData;

class Discount extends Template
{
    protected Order $_order;
    protected DataObject $_source;
    protected HelperData $_helperData;
    protected GiftCardHistoryFactory $_giftCardHistoryFactory;

    public function __construct(
        Context                $context,
        HelperData             $helperData,
        GiftCardHistoryFactory $giftCardHistoryFactory,
        array                  $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helperData = $helperData;
        $this->_giftCardHistoryFactory = $giftCardHistoryFactory;
    }

    public function initTotals(): Discount
    {
        $parentBlock = $this->getParentBlock();
        $this->_order = $parentBlock->getOrder();
        $incrementId = $this->_order->getIncrementId();
        $historyAction = "Use for Order #$incrementId";
        $giftCard = $this->_giftCardHistoryFactory->create()->load($historyAction, 'action');
        if ($giftCard->getId()) {
            $giftCardAmount = $giftCard->getAmount();
            if ($giftCardAmount) {
                $discount = new DataObject(
                    [
                        'code' => 'giftcard_discount',
                        'value' => $this->_helperData->formatCurrencyByCode($giftCardAmount,
                            $this->_order->getOrderCurrencyCode()),
                        'base_value' => $giftCardAmount,
                        'label' => __('Gift Card'), true
                    ]
                );
                $parentBlock->addTotalBefore($discount, 'grand_total');
            }
        }
        return $this;
    }
}