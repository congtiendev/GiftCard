<?php

namespace Mageplaza\Affiliate\Model;

use Mageplaza\Affiliate\Model\ResourceModel\Account as ResourceModelAccount;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Account extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'mageplaza_affiliate_account';

    protected $_cacheTag = 'mageplaza_affiliate_account';

    protected $_eventPrefix = 'mageplaza_affiliate_account';

    protected function _construct()
    {
        $this->_init(ResourceModelAccount::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues(): array
    {
        return [];
    }


    public function isExitsCustomer($customerId)
    {
        return $this->getResource()->isExitsCustomer($customerId);
    }

    public function getAccountByCode($code)
    {
        return $this->getResource()->getAccountByCode($code);
    }

    public function getAccountName($accountId)
    {
        return $this->getResource()->getAccountName($accountId);
    }

    public function getAccountEmail($accountId)
    {
        return $this->getResource()->getAccountEmail($accountId);
    }

}