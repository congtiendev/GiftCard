<?php

namespace Mageplaza\Affiliate\Ui\Component\Listing\Account\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) { //  Kiểm tra xem có dữ liệu không
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name'); // Lấy tên cột hiện tại
                if (isset($item['account_id'])) {
                    $item[$name] = $item['status'] === 1 ? 'Active' : 'Inactive';
                }
            }
        }
        return $dataSource;
    }
}