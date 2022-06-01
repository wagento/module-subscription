<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Ui\DataProvider\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;

class SubscriptionSales extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * SubscriptionSales constructor.
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'wagento_subscription_order',
        $resourceModel = '\Wagento\Subscription\Model\ResourceModel\SubscriptionSales::class'
    ) {

        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Initialize select function
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        $customerTable = $this->getTable('customer_grid_flat');
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $wagentoSubProductTable = $this->getTable('wagento_subscription_products');
        $wagentoSubTable = $this->getTable('wagento_subscription');

        $this->getSelect()->join(
            $salesOrderItemTable . ' as soi',
            "main_table.sub_order_item_id = soi.item_id && soi.is_subscribed = 1",
            ['*', 'created_at as order_created_at', 'updated_at as order_updated_at']
        );

        $this->getSelect()->join(
            $customerTable . ' as customer',
            'main_table.customer_id = customer.entity_id',
            ['customer.name as customer_name']
        );

        $this->getSelect()->join(
            $wagentoSubProductTable . ' as wsp',
            "soi.product_id = wsp.product_id",
            ['subscription_id']
        );

        $this->getSelect()->join(
            $wagentoSubTable . ' as ws',
            "wsp.subscription_id = ws.subscription_id",
            ['name', 'frequency', 'fee', 'discount']
        );

        $this->addFilterToMap('id', 'main_table.id')
            ->addFilterToMap('customer_name', 'customer.name')
            ->addFilterToMap('name', 'ws.name')
            ->addFilterToMap('created_at', 'main_table.created_at')
            ->addFilterToMap('store_id', 'main_table.store_id');

        return $this;
    }
}
