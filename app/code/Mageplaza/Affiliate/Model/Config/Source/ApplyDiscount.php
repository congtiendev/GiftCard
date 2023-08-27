<?php

namespace Mageplaza\Affiliate\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ApplyDiscount implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'no', 'label' => __('No')],
            ['value' => 'fixed', 'label' => __('Fixed Value')],
            ['value' => 'percentage', 'label' => __('Percentage of order total')]
        ];
    }

}