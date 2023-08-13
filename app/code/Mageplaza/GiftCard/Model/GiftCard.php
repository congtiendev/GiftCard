<?php

namespace Mageplaza\GiftCard\Model;

use Mageplaza\GiftCard\Model\ResourceModel\GiftCard as ResourceModelGiftCard;

class GiftCard extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'mageplaza_giftcard_giftcard';

    protected $_cacheTag = 'mageplaza_giftcard_giftcard';

    protected $_eventPrefix = 'mageplaza_giftcard_giftcard';

    protected function _construct()
    {
        $this->_init(ResourceModelGiftCard::class);
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
