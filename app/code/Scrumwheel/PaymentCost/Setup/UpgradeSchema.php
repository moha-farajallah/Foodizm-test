<?php

namespace Scrumwheel\PaymentCost\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();


        foreach(['quote', 'quote_address', 'sales_order', 'sales_invoice', 'sales_creditmemo'] as $table){
            $this->addColumn($setup, $table, 'fee_amount', 'Fee Amount');
            $this->addColumn($setup, $table, 'base_fee_amount', 'Base Fee Amount');
        }


        $this->addColumn($setup, 'sales_order', 'fee_amount_invoiced', 'Fee Amount Invoiced');
        $this->addColumn($setup, 'sales_order', 'base_fee_amount_invoiced', 'Base Fee Amount Invoiced');
        $this->addColumn($setup, 'sales_order', 'fee_amount_refunded', 'Fee Amount Refunded');
        $this->addColumn($setup, 'sales_order', 'base_fee_amount_refunded', 'Base Fee Amount Refunded');

        $setup->endSetup();
    }

    public function addColumn(SchemaSetupInterface $setup, $table, $name, $description){
        $setup->getConnection()->addColumn(
            $setup->getTable($table),
            $name,
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'default' => 0.0000,
                'nullable' => true,
                'comment' => $description
            ]
        );
    }
}
