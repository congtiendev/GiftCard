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

    /**
     * @throws LocalizedException
     */

    public function getAccountByCode($code): string
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('code = ?', $code);
        return $connection->fetchOne($select);
    }

    public function getAccountName($accountId): string
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(['a' => $this->getMainTable()])
            ->join(
                ['c' => $this->getTable('customer_entity')],
                'a.customer_id = c.entity_id',
                ['firstname', 'lastname']
            )
            ->where('a.account_id = ?', $accountId);

        $result = $connection->fetchRow($select);
        if ($result) {
            return $result['firstname'] . ' ' . $result['lastname'];
        }
        return '';
    }

}