<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade Intenso Reviews DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addReviewImage($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addStoreOwnerComments($setup);
            $this->addStoreIdColumnToMap($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addIpIndex($setup);
        }

        $setup->endSetup();
    }

    /**
     * Create table 'intenso_review_image'
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addReviewImage(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('intenso_review_image'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Image id'
            )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Review entity ID'
            )
            ->addColumn(
                'file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Image file'
            )
            ->addIndex(
                $setup->getIdxName('intenso_review_image', ['review_id']),
                ['review_id']
            )
            ->addForeignKey(
                $setup->getFkName('intenso_review_image', 'review_id', 'review', 'review_id'),
                'review_id',
                $setup->getTable('review'),
                'review_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Intenso Review Images');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Create table 'intenso_review_storeowner_comment'
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addStoreOwnerComments(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('intenso_review_storeowner_comment'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Image id'
            )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Review entity ID'
            )
            ->addColumn(
                'text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Comment text'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Type (i.e. private|public)'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Comment create date'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Comment update date'
            )
            ->addIndex(
                $setup->getIdxName('intenso_review_storeowner_comment', ['review_id']),
                ['review_id']
            )
            ->addForeignKey(
                $setup->getFkName('intenso_review_storeowner_comment', 'review_id', 'review', 'review_id'),
                'review_id',
                $setup->getTable('review'),
                'review_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Intenso Review Store Owner Comments');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Add 'store_id' column to 'intenso_review_map' table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addStoreIdColumnToMap(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('intenso_review_map'),
            'store_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'   => null,
                'nullable' => false,
                'unsigned' => true,
                'default'  => 0,
                'comment'  => 'Store Id'
            ]
        );
    }

    /**
     * Add index to IP field in reviews userdata table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addIpIndex(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('intenso_review_userdata'),
            $setup->getIdxName('intenso_review_userdata', ['ip'], AdapterInterface::INDEX_TYPE_INDEX),
            ['ip'],
            AdapterInterface::INDEX_TYPE_INDEX
        );
    }
}
