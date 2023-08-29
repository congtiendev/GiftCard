<?php

namespace Mageplaza\Affiliate\Ui\Component\Listing\History\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['history_id']) && $item['is_admin_change'] == 1) {
                    $item[$name] = $item['status'] ? 'Active' : 'Inactive';
                }
            }
        }
        return $dataSource;
    }
}