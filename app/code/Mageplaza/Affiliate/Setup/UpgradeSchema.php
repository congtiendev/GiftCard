<?php

namespace Mageplaza\Affiliate\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            // Add 'discount' column to affiliate_history table
            $tableName = $setup->getTable('affiliate_history');
            if ($setup->getConnection()->isTableExists($tableName)) {
                $setup->getConnection()->addColumn(
                    $tableName,
                    'discount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'default' => 0,
                        'nullable' => false,
                        'comment' => 'Affiliate Discount',
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
