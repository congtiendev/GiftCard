<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;
use Magento\Framework\DataObject;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Mageplaza\Affiliate\Helper\Data as HelperData;


class Discount extends Template
{
    protected Order $_order;
    protected DataObject $_source;
    protected HelperData $_helperData;
    protected HistoryFactory $_historyFactory;

    public function __construct(
        Context        $context,
        HelperData     $helperData,
        HistoryFactory $historyFactory,
        array          $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_helperData = $helperData;
        $this->_historyFactory = $historyFactory;
    }


    public function initTotals(): Discount
    {
        $parentBlock = $this->getParentBlock();
        $this->_order = $parentBlock->getOrder();
        $history = $this->_historyFactory->create()->load($this->_order->getId(), 'order_id');
        if ($history->getId()) {
            if ($history->getAmount()) {
                $commission = new DataObject(
                    [
                        'code' => 'affiliate_commission',
                        'strong' => false,
                        'base_value' => $history->getAmount(),
                        'value' => $this->_helperData->formatCurrencyByCode($history->getAmount(),
                            $this->_order->getOrderCurrencyCode()),
                        'label' => __('Affiliate Commission'),
                    ]
                );
                $parentBlock->addTotal($commission, 'affiliate_commission');
            }
            if ($history->getDiscount()) {
                $discount = new DataObject(
                    [
                        'code' => 'affiliate_discount',
                        'strong' => false,
                        'base_value' => -$history->getDiscount(),
                        'value' => $this->_helperData->formatCurrencyByCode(-$history->getDiscount(),
                            $this->_order->getOrderCurrencyCode()),
                        'label' => __('Affiliate Discount'),
                    ]
                );
                $parentBlock->addTotal($discount, 'affiliate_discount');
            }
        }
        return $this;
    }
}
