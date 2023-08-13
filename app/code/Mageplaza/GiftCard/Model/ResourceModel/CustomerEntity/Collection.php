<?php

namespace Mageplaza\GiftCard\Model\ResourceModel\CustomerEntity;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\GiftCard\Model\CustomerEntity as ModelCustomerEntity;
use Mageplaza\GiftCard\Model\ResourceModel\CustomerEntity as ResourceModelCustomerEntity;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'mageplaza_giftcard_customer_entity_collection';
    protected $_eventObject = 'customer_entity_collection';

    protected function _construct()
    {
        $this->_init(ModelCustomerEntity::class, ResourceModelCustomerEntity::class);
    }

}