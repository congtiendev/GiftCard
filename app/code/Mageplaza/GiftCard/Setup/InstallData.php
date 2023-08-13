<?php

namespace Mageplaza\GiftCard\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $data = [
            [
                'code' => 'GIFT001',
                'balance' => 100.0000,
                'amount_used' => 0.0000,
                'created_from' => 'admin',
            ],
            [
                'code' => 'GIFT002',
                'balance' => 200.0000,
                'amount_used' => 0.0000,
                'created_from' => 'admin',
            ],
            [
                'code' => 'GIFT003',
                'balance' => 300.0000,
                'amount_used' => 0.0000,
                'created_from' => 'admin',
            ],
            [
                'code' => 'GIFT004',
                'balance' => 400.0000,
                'amount_used' => 0.0000,
                'created_from' => 'admin',
            ],
            [
                'code' => 'GIFT005',
                'balance' => 500.0000,
                'amount_used' => 0.0000,
                'created_from' => 'admin',
            ],
        ];

        $table = $setup->getTable('giftcard_code'); // Thay 'giftcard' bằng 'giftcard_code'
        $columns = ['code', 'balance', 'amount_used', 'created_from']; // Tên các cột trong bảng

        $setup->getConnection()->insertArray($table, $columns, $data); // Sử dụng insertArray thay vì insert

        $setup->endSetup();
    }
}
