<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'wagento_subscription'
         */
        if(!$installer->tableExists('wagento_subscription')) {
            $subtableName = $setup->getTable('wagento_subscription');
            $subtable = $installer->getConnection()->newTable(
                $installer->getTable($subtableName)
            )->addColumn(
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Subscription Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )->addColumn(
                'frequency',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Frequency'
            )->addColumn(
                'fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Fee'
            )->addColumn(
                'how_many',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true,'nullable' => false,'default' => '0'],
                'No of Subscription Cycles'
            )->addColumn(
                'discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => true, 'default' => 0.00],
                'Discount'
            )->addIndex(
                $installer->getIdxName(
                    $subtableName,
                    ['name'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment(
                'Wagento Subscription'
            );
            $installer->getConnection()->createTable($subtable);
        }

        /**
         * Create table 'wagento_subscription_products'
         */
        if(!$installer->tableExists('wagento_subscription_products')) {
            $subProductstableName = $setup->getTable('wagento_subscription_products');
            $subProductstable = $installer->getConnection()->newTable(
                $installer->getTable($subProductstableName)
            )->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Subscription Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product ID'
            )->addIndex(
                $installer->getIdxName('wagento_subscription_products', ['subscription_id', 'product_id']),
                ['subscription_id', 'product_id']
            )->addForeignKey(
                $installer->getFkName('wagento_subscription_products', 'subscription_id', 'wagento_subscription', 'subscription_id'),
                'subscription_id',
                $installer->getTable('wagento_subscription'),
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Wagento Subscription Products'
            );
            $installer->getConnection()->createTable($subProductstable);
        }


        /**
         * Create table 'wagento_subscription_order'
         */
        if(!$installer->tableExists('wagento_subscription_order')) {
            $subOrdertableName = $setup->getTable('wagento_subscription_order');
            $subOrdertable = $installer->getConnection()->newTable(
                $installer->getTable($subOrdertableName)
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true,'unsigned' => true,'nullable' => false,'primary' => true],
                'Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => false],
                'Customer Id'
            )->addColumn(
                'subscribe_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => false],
                'Subscriber\'s order id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )->addColumn(
                'last_renewed',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Last Renewed At'
            )->addColumn(
                'next_renewed',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Next Renewed At'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Updated At'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                '5',
                ['nullable' => false],
                'Store Id'
            )->addColumn(
                'sub_start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                '5',
                ['nullable' => false],
                'Subscription Start Date'
            )->addColumn(
                'sub_order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '11',
                ['nullable' => false],
                'Subscription Order Item Id'
            )->addColumn(
                'how_many',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '12',
                ['default' => null, 'nullable' => true],
                'No of Subscription Cycles'
            )->addColumn(
                'billing_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '12',
                ['default' => null,'nullable' => true],
                'Wagento subscription number of times billing'
            )->addColumn(
                'billing_address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '12',
                ['default' => null,'nullable' => true],
                'If Customer Change Billing Address'
            )->addColumn(
                'shipping_address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '12',
                ['default' => null,'nullable' => true],
                'If Customer Change Shipping Address'
            )->addColumn(
                'public_hash',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null,'nullable' => true],
                'If Customer Change Credit Card Details'
            )->addColumn(
                'sub_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                ['nullable' => false],
                'Subscription Plan Name'
            )->addColumn(
                'sub_frequency',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                '5',
                ['nullable' => false],
                'Subscription Frequency'
            )->addColumn(
                'sub_fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Subscription Initial Fee'
            )->addColumn(
                'sub_discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Subscription Discount Amount'
            )->addColumn(
                'sub_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '10',
                [],
                'Subscription Product Id'
            )->setComment('Braintree Subscription Order Table')
                ->setOption('type', 'InnoDB');
            $installer->getConnection()->createTable($subOrdertable);
        }


        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $invoiceTable = 'sales_invoice';
        $creditMemoTable = 'sales_creditmemo';

        /*Quote Table*/
        $installer->getConnection()
            ->addColumn(
                $installer->getTable($quoteTable),
                'initial_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'default' => null,
                    'nullable' => true,
                    'comment' => 'Wagento Subscription Initial Fee'
                ]
            );

        //Order table
        $installer->getConnection()
            ->addColumn(
                $installer->getTable($orderTable),
                'initial_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'default' => null,
                    'nullable' => true,
                    'comment' => 'Wagento Subscription Initial Fee'

                ]
            );

        //Invoice table
        $installer->getConnection()
            ->addColumn(
                $installer->getTable($invoiceTable),
                'initial_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'default' => null,
                    'nullable' => true,
                    'comment' => 'Wagento Subscription Initial Fee'

                ]
            );

        //Credit memo table
        $installer->getConnection()
            ->addColumn(
                $installer->getTable($creditMemoTable),
                'initial_fee',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'default' => null,
                    'nullable' => true,
                    'comment' => 'Wagento Subscription Initial Fee'

                ]
            );

        $quoteItemTable = $installer->getTable('quote_item');
        $columns = [
            'is_subscribed' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'comment' => 'Check product is subscribed or not by customer',
            ],

        ];
        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($quoteItemTable, $name, $definition);
        }

        /**/
        $salesOrderItemTable = $installer->getTable('sales_order_item');
        $columns = [
            'is_subscribed' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'comment' => 'Check product is subscribed or not by customer',
            ],

        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($salesOrderItemTable, $name, $definition);
        }
        $installer->endSetup();
    }
}
