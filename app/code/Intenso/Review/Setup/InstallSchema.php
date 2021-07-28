<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        /**
         * Create table 'intenso_review_comment'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('intenso_review_comment'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Comment id'
            )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Review id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer id'
            )
            ->addColumn(
                'nickname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'User nickname'
            )
            ->addColumn(
                'text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Review text'
            )
            ->addColumn(
                'status_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Enabled'
            )
            ->addColumn(
                'guest_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Email'
            )
            ->addColumn(
                'email_sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Notification email sent'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'IP address'
            )
            ->addColumn(
                'http_user_agent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'HTTP User Agent'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Comment create date'
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_comment', ['entity_id']),
                ['entity_id']
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_comment', ['review_id']),
                ['review_id']
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_comment', ['status_id']),
                ['status_id']
            )
            ->addForeignKey(
                $installer->getFkName('intenso_review_comment', 'review_id', 'review', 'review_id'),
                'review_id',
                $installer->getTable('review'),
                'review_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Intenso Review Comment');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'intenso_review_vote'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('intenso_review_vote'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Vote id'
            )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Review id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Customer Id'
            )
            ->addColumn(
                'guest_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Guest Id'
            )
            ->addColumn(
                'helpful',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Helpful votes'
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_vote', ['review_id']),
                ['review_id']
            )
            ->addForeignKey(
                $installer->getFkName('intenso_review_vote', 'review_id', 'review', 'review_id'),
                'review_id',
                $installer->getTable('review'),
                'review_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Review votes');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'intenso_review_summary'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('intenso_review_summary'))
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Review id'
            )
            ->addColumn(
                'rating_summary',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rating Summary'
            )
            ->addColumn(
                'helpful',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Helpful'
            )
            ->addColumn(
                'nothelpful',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Not Helpful'
            )
            ->addColumn(
                'comments',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Comments'
            )
            ->addColumn(
                'guest_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Email'
            )
            ->addColumn(
                'verified_purchase',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Verified Purchase'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'IP address'
            )
            ->addColumn(
                'http_user_agent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'HTTP User Agent'
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_summary', ['review_id']),
                ['review_id']
            )
            ->addForeignKey(
                $installer->getFkName('intenso_review_summary', 'review_id', 'review', 'review_id'),
                'review_id',
                $installer->getTable('review'),
                'review_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Review summary');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'intenso_review_userdata'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('intenso_review_userdata'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity id'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'IP address'
            )
            ->addColumn(
                'http_user_agent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'HTTP User Agent'
            )
            ->addColumn(
                'cookie',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Cookie'
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_userdata', ['entity_id']),
                ['entity_id']
            )
            ->setComment('Userdata');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'intenso_review_map'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('intenso_review_map'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Mail After Purchase id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Order Id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'customer_token',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'Customer token'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Sort order'
            )
            ->addColumn(
                'email_sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Email Sent'
            )
            ->addColumn(
                'sent_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Sent date'
            )
            ->addColumn(
                'review_posted',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Review posted'
            )
            ->addColumn(
                'review_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Review date'
            )
            ->addColumn(
                'problem_error_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => '0'],
                'Problem Error Code'
            )
            ->addColumn(
                'problem_error_text',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'Problem Error Text'
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_map', ['created_at']),
                ['created_at']
            )
            ->addIndex(
                $installer->getIdxName('intenso_review_map', ['customer_token']),
                ['customer_token']
            )
            ->addForeignKey(
                $installer->getFkName('intenso_review_map', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Mail After Purchase');
        $installer->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
