<?php

namespace Mageplaza\Affiliate\Model\ResourceModel\Account;

use Mageplaza\Affiliate\Model\Account as ModelAccount;
use Mageplaza\Affiliate\Model\ResourceModel\Account as ResourceModelAccount;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'account_id';
    protected $_eventPrefix = 'mageplaza_affiliate_account_collection';
    protected $_eventObject = 'account_collection';

    protected function _construct()
    {
        $this->_init(ModelAccount::class, ResourceModelAccount::class);
    }
}