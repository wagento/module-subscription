<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SubscriptionOrder extends \Magento\Customer\Block\Account\Dashboard
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory
     */
    protected $subscriptionOrderFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * SubscriptionOrder constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $subscriptionOrderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $subscriptionOrderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        AccountManagementInterface $customerAccountManagement,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->subscriptionOrderFactory = $subscriptionOrderFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    /**
     * Get subscriptions function.
     *
     * @return mixed
     */
    public function getSubscriptions()
    {
        $customerId = $this->customerSessionFactory->create()->getCustomerId();
        $connection = $this->resource->getConnection();
        $salesOrderItemTable = $connection->getTableName('sales_order_item');
        $wagentoSubProductTable = $connection->getTableName('wagento_subscription_products');
        $customerTable = $connection->getTableName('customer_entity');
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
        if (null != $customerId) {
            $collectionSubscriptions = $this->subscriptionOrderFactory->create();

            $collectionSubscriptions->getSelect()->where(
                'main_table.customer_id='.$customerId
            );

            $collectionSubscriptions->getSelect()->join(
                $salesOrderItemTable.' as soi',
                'main_table.sub_order_item_id = soi.item_id && soi.is_subscribed = 1',
                ['*', 'created_at as order_created_at', 'updated_at as order_updated_at']
            );

            $collectionSubscriptions->getSelect()->join(
                $customerTable.' as customer',
                'main_table.customer_id = customer.entity_id',
                ['firstname', 'lastname', 'email']
            )->columns(new \Zend_Db_Expr("CONCAT(`customer`.`firstname`, ' ',`customer`.`lastname`) AS customer_name"));

            $collectionSubscriptions->getSelect()->join(
                $wagentoSubProductTable.' as wsp',
                'soi.product_id = wsp.product_id',
                ['subscription_id']
            );
            $collectionSubscriptions->setOrder('main_table.id', 'DESC');
            $collectionSubscriptions->setPageSize($pageSize);
            $collectionSubscriptions->setCurPage($page);
            $collectionSubscriptions->setOrder('id', 'DESC');

            return $collectionSubscriptions;
        }
    }

    /**
     * Return the pager of the grid.
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare layout function.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Subscriptions'));

        if ($this->getSubscriptions()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'seller.product.list.pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15])->setShowPerPage(true)->setCollection(
                $this->getSubscriptions()
            );
            $this->setChild('pager', $pager);
            $this->getSubscriptions()->load();
        }

        return $this;
    }
}
