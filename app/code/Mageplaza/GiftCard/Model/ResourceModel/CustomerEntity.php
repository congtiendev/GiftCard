<?php

namespace Mageplaza\GiftCard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class CustomerEntity extends AbstractDb
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('customer_entity', 'entity_id');
    }
}