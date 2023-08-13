<?php

namespace Mageplaza\GiftCard\Model\ResourceModel\GiftCard;

use Mageplaza\GiftCard\Model\GiftCard as ModelGiftCard;
use Mageplaza\GiftCard\Model\ResourceModel\GiftCard as ResourceModelGiftCard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'giftcard_id'; // khóa chính của bảng
    protected $_eventPrefix = 'mageplaza_giftcard_giftcard_collection'; // khai báo prefix cho các sự kiện của collection
    // model
    protected $_eventObject = 'giftcard_collection'; // khai báo object cho các sự kiện của collection model


    protected function _construct()
    {
        $this->_init(ModelGiftCard::class, ResourceModelGiftCard::class);
    }

}