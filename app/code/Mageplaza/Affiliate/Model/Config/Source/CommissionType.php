<?php

namespace Mageplaza\Affiliate\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CommissionType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'fixed', 'label' => __('Fixed Value')],
            ['value' => 'percentage', 'label' => __('Percentage of order total')]
        ];
    }
}

?>