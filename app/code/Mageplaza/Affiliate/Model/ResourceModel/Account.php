<?php

namespace Mageplaza\Affiliate\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Account extends AbstractDb
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('affiliate_account', 'account_id');
    }

    /**
     * @throws LocalizedException
     */
    public function isExitsCustomer($customerId): string
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('customer_id = ?', $customerId);
        return $connection->fetchOne($select);
    }
}