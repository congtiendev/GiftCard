<?php

namespace Mageplaza\GiftCard\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $connection = $installer->getConnection();

            // Create table 'giftcard_history'
            $table = $installer->getTable('giftcard_history');
            if (!$connection->isTableExists($table)) {
                $giftcardHistoryTable = $connection->newTable($table)
                    ->addColumn(
                        'history_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'History ID'
                    )
                    ->addColumn(
                        'giftcard_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Gift Card ID'
                    )
                    ->addColumn(
                        'customer_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Customer ID'
                    )
                    ->addColumn(
                        'amount',
                        Table::TYPE_DECIMAL,
                        '12,4',
                        ['nullable' => false, 'default' => 0.0000],
                        'Amount'
                    )
                    ->addColumn(
                        'action',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'Action'
                    )
                    ->addColumn(
                        'action_time',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Action Time'
                    )
                    ->addForeignKey(
                        $installer->getFkName('giftcard_history', 'giftcard_id', 'giftcard_code', 'giftcard_id'),
                        'giftcard_id',
                        $installer->getTable('giftcard_code'),
                        'giftcard_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName('giftcard_history', 'customer_id', 'customer_entity', 'entity_id'),
                        'customer_id',
                        $installer->getTable('customer_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('Gift Card History Table');
                $connection->createTable($giftcardHistoryTable);
            }
        }
        $installer->endSetup();
    }
}
