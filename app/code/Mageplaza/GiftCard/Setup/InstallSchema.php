<?php

namespace Mageplaza\GiftCard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('giftcard_code');

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'giftcard_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Giftcard ID'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Giftcard Code'
                )
                ->addColumn(
                    'balance',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false],
                    'Giftcard Balance'
                )
                ->addColumn(
                    'amount_used',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Amount Used'
                )
                ->addColumn(
                    'created_from',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => 'admin'],
                    'Created From'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Giftcard Table');

            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}