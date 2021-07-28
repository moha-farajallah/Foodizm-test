<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
    */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $salesOrder = $installer->getTable('sales_order');
        $connection->addColumn(
            $salesOrder,
            'ks_delivery_info',
            ['type' => Table::TYPE_TEXT, 'visible' => false, 'comment' => 'Ksolves Deliverydate Info']
        );
        $installer->endSetup();
    }
}
