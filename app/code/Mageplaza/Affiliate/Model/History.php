<?php

namespace Mageplaza\Affiliate\Model;

use Mageplaza\Affiliate\Model\ResourceModel\History as ResourceModelHistory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class History extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'mageplaza_affiliate_history';
    protected $_cacheTag = 'mageplaza_affiliate_history';

    protected $_eventPrefix = 'mageplaza_affiliate_history';

    protected function _construct()
    {
        $this->_init(ResourceModelHistory::class);
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