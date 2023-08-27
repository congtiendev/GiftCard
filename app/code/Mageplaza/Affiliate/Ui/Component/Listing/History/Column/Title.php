<?php

namespace Mageplaza\Affiliate\Ui\Component\Listing\History\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Title extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['is_admin_change'])) {
                    if ($item['is_admin_change']) {
                        $item['title'] = __('Changed by Admin');
                    } else {
                        $orderId = $item['order_increment_id'] ?? '';
                        $item['title'] = __('Created from order #%1', $orderId);
                    }
                }
            }
        }
        return $dataSource;
    }
}