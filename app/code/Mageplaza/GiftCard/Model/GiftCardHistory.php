<?php

namespace Mageplaza\GiftCard\Model;

use Mageplaza\GiftCard\Model\ResourceModel\GiftCardHistory as ResourceModelGiftCardHistory;

class GiftCardHistory extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'mageplaza_giftcard_giftcard_history';

    protected $_cacheTag = 'mageplaza_giftcard_giftcard_history';

    protected $_eventPrefix = 'mageplaza_giftcard_giftcard_history';

    protected function _construct()
    {
        $this->_init(ResourceModelGiftCardHistory::class);
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
