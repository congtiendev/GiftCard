<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Sales\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\AbstractBlock;
use Mageplaza\Affiliate\Model\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order;

class Totals extends AbstractBlock
{
    protected HistoryFactory $_historyFactory;
    protected OrderFactory $_orderFactory;

    public function __construct(
        Context        $context,
        HistoryFactory $historyFactory,
        OrderFactory   $orderFactory,
        array          $data = []
    )
    {
        $this->_historyFactory = $historyFactory;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

}