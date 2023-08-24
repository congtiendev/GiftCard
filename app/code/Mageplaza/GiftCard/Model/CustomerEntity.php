<?php

namespace Mageplaza\GiftCard\Model;

use Mageplaza\GiftCard\Model\ResourceModel\CustomerEntity as ResourceModelCustomerEntity;

class CustomerEntity extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface
{
    public const CACHE_TAG = 'mageplaza_giftcard_customer_entity';

    protected $_cacheTag = 'mageplaza_giftcard_customer_entity';

    protected $_eventPrefix = 'mageplaza_giftcard_customer_entity';

    protected function _construct()
    {
        $this->_init(ResourceModelCustomerEntity::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues(): array
    {
        return [];
    }

}
