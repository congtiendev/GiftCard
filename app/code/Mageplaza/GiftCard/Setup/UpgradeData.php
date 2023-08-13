<?php

namespace Mageplaza\GiftCard\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;


class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $setup->startSetup();
            $data = [
                'giftcard_id' => 44,
                'customer_id' => 2,
                'amount' => 69,
                'action' => 'create',
                'action_time' => '9/8/23'
            ];

            $table = $setup->getTable('giftcard_history');
            $setup->getConnection()->insert($table, $data);
            $setup->endSetup();
        }
    }
}