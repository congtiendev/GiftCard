<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;
use Magento\Framework\DataObject;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Mageplaza\Affiliate\Helper\Data as HelperData;


class Totals extends Template
{
    protected Order $_order;
    protected DataObject $_source;
    protected PriceHelper $_priceHelper;
    protected HelperData $_helperData;
    protected HistoryFactory $_historyFactory;

    public function __construct(
        Context        $context,
        PriceHelper    $priceHelper,
        HelperData     $helperData,
        HistoryFactory $historyFactory,
        array          $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_priceHelper = $priceHelper;
        $this->_helperData = $helperData;
        $this->_historyFactory = $historyFactory;
    }


    public function initTotals(): Totals
    {
        if (!$this->_helperData->isAffiliateEnabled()) {
            return $this;
        }
        $parentBlock = $this->getParentBlock();
        $this->_order = $parentBlock->getOrder();
        $history = $this->_historyFactory->create()->load($this->_order->getId(), 'order_id');
        if ($history->getId()) {
            $commission = new DataObject(
                [
                    'code' => 'affiliate_commission',
                    'strong' => true,
                    'base_value' => $history->getAmount(),
                    'value' => $this->_priceHelper->currency($history->getAmount(), true, false),
                    'label' => __('Affiliate Commission'),
                ]
            );
            $discount = new DataObject(
                [
                    'code' => 'affiliate_discount',
                    'strong' => true,
                    'base_value' => $history->getDiscount(),
                    'value' => $this->_priceHelper->currency($history->getDiscount(), true, false),
                    'label' => __('Affiliate Discount'),
                ]
            );
            $parentBlock->addTotal($commission, 'affiliate_commission');
            $parentBlock->addTotal($discount, 'affiliate_discount');
        }
        return $this;
    }
}
