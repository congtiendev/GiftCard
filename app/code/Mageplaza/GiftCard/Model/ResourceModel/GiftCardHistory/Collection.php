<?php

namespace Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory;

use Mageplaza\GiftCard\Model\GiftCardHistory as ModelGiftCardHistory;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory as ResourceModelGiftCardHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'history_id'; // khóa chính của bảng
    protected $_eventPrefix = 'mageplaza_giftcard_giftcard_history_collection'; // khai báo prefix cho các sự kiện của
    // collection
    // model
    protected $_eventObject = 'giftcard_history_collection'; // khai báo object cho các sự kiện của collection model

    protected function _construct()
    {
        $this->_init(ModelGiftCardHistory::class, ResourceModelGiftCardHistory::class);
    }

    public function getListHistory($customerId): Collection
    {
        $this->getSelect()->joinLeft(
            ['giftcard_code' => $this->getTable('giftcard_code')],
            'main_table.giftcard_id = giftcard_code.giftcard_id',
            ['code']
        )->order('main_table.action_time DESC');
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }


}