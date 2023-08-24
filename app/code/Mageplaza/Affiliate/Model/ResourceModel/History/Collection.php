<?php

namespace Mageplaza\Affiliate\Model\ResourceModel\History;

use Mageplaza\Affiliate\Model\History as ModelHistory;
use Mageplaza\Affiliate\Model\ResourceModel\History as ResourceModelHistory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'history_id';
    protected $_eventPrefix = 'mageplaza_affiliate_history_collection';
    protected $_eventObject = 'history_collection';

    protected function _construct()
    {
        $this->_init(ModelHistory::class, ResourceModelHistory::class);
    }
}