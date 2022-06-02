<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Model\CustomerFactory;
use Wagento\Subscription\Helper\Data as subscriptionHelperData;

class SubscriptionView extends \Magento\Customer\Block\Account\Dashboard
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Wagento\Subscription\Model\ResourceModel\SubscriptionSalesFactory
     */
    protected $_subscriptionOrderFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $helperPrice;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrder;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var subscriptionHelperData
     */
    protected $subscriptionHelperData;

    /**
     * @var customerSessionFactory
     */
    protected $customerSessionFactory;

    /**
     * SubscriptionView constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $subscriptionOrderFactory
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Pricing\Helper\Data $helperPrice
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Model\OrderRepository $salesOrder
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param EncryptorInterface $encryptor
     * @param CustomerFactory $customer
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param subscriptionHelperData $subscriptionHelperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $subscriptionOrderFactory,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Pricing\Helper\Data $helperPrice,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderRepository $salesOrder,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        PaymentTokenManagementInterface $paymentTokenManagement,
        EncryptorInterface $encryptor,
        CustomerFactory $customer,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        subscriptionHelperData $subscriptionHelperData,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        array $data = []
    ) {
        $this->_stockState = $stockState;
        $this->_subscriptionOrderFactory = $subscriptionOrderFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->orderRepository = $orderRepository;
        $this->helperPrice = $helperPrice;
        $this->_resource = $resource;
        $this->salesOrder = $salesOrder;
        $this->addressRenderer = $addressRenderer;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->encryptor = $encryptor;
        $this->customer = $customer;
        $this->countryFactory = $countryFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->subscriptionHelperData = $subscriptionHelperData;
        parent::__construct($context, $customerSession, $subscriberFactory, $customerRepository, $customerAccountManagement, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $id = $this->getRequest()->getParam('order_id');
        $getAction = $this->getRequest()->getActionName();
        if ($getAction == 'edit') {
            $this->pageConfig->getTitle()->set(__('Edit Subscription Profile #') . $id);
        } else {
            $this->pageConfig->getTitle()->set(__('Subscription Profile #') . $id);
        }

        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getSubscriptions()
    {
        $customerId = $this->customerSessionFactory->create()->getCustomerId();
        $id = $this->getRequest()->getParam('order_id');
        $connection = $this->_resource->getConnection();
        $salesOrderItemTable = $connection->getTableName('sales_order_item');
        $salesOrderTable = $connection->getTableName('sales_order');
        $wagentoSubProductTable = $connection->getTableName('wagento_subscription_products');
        $customerTable = $connection->getTableName('customer_entity');
        if ($customerId != null) {
            $collectionSubscriptions = $this->_subscriptionOrderFactory->create();

            $collectionSubscriptions->addFieldToFilter('id', ['eq' => $id]);
            $collectionSubscriptions->addFieldToFilter('main_table.customer_id', ['eq' => $customerId]);

            $collectionSubscriptions->getSelect()->join(
                $salesOrderTable . ' as so',
                "main_table.subscribe_order_id = so.entity_id",
                ['billing_address_id as order_billing_address', 'shipping_address_id as order_shipping_address']
            );

            $collectionSubscriptions->getSelect()->join(
                $salesOrderItemTable . ' as soi',
                "main_table.sub_order_item_id = soi.item_id && soi.is_subscribed = 1",
                ['*', 'created_at as order_created_at', 'updated_at as order_updated_at']
            );

            $collectionSubscriptions->getSelect()->join(
                $customerTable . ' as customer',
                'main_table.customer_id = customer.entity_id',
                ['firstname', 'lastname', 'email']
            )
                ->columns(new \Zend_Db_Expr("CONCAT(`customer`.`firstname`, ' ',`customer`.`lastname`) AS customer_name"));

            $collectionSubscriptions->getSelect()->join(
                $wagentoSubProductTable . ' as wsp',
                "soi.product_id = wsp.product_id",
                ['subscription_id']
            );
        }
        return $collectionSubscriptions->getFirstItem();
    }

    /**
     * @param $orderId
     * @return null|string
     */
    public function getShippingAddress($orderId)
    {
        $_order = $this->salesOrder->get($orderId);
        $shippingId = $this->getSubscriptions()->getShippingAddressId();
        if (isset($shippingId) && $shippingId != 0) {
            return $this->subscriptionHelperData->getSubCustomerAddress($shippingId, 'html');
        } else {
            $shippingAddress = $_order->getShippingAddress();
            $address = '';
            if (isset($shippingAddress)) {
                $address = $this->addressRenderer->format($shippingAddress, 'html');
            }
            return $address;
        }
    }

    /**
     * @param $orderId
     * @return null|string
     */
    public function getBillingAddress($orderId)
    {
        $_order = $this->salesOrder->get($orderId);
        $billingId = $this->getSubscriptions()->getBillingAddressId();
        if (isset($billingId) && $billingId != 0) {
            return $this->subscriptionHelperData->getSubCustomerAddress($billingId, 'html');
        } else {
            $billingAddress = $_order->getBillingAddress();
            return $this->addressRenderer->format($billingAddress, 'html');
        }
    }

    /**
     * @param $orderId
     * @return null|string
     */
    public function getShippingMethod($orderId)
    {
        $_order = $this->salesOrder->get($orderId);
        return $_order->getShippingDescription();
    }

    /**
     * @param $orderId
     * @return string[]
     */
    public function getPaymentMethod($orderId, $publicHash, $customerId)
    {
        if (isset($publicHash)) {
            return $this->subscriptionHelperData->getCard($customerId, $publicHash);
        }
        $_order = $this->salesOrder->get($orderId);
        $details = [];
        $additionalInfo = $_order->getPayment()->getAdditionalInformation();
        if (isset($additionalInfo['cc_number']) && isset($additionalInfo['cc_type'])) {
            $details['cc_number'] = $additionalInfo['cc_number'];
            $details['cc_type'] = $additionalInfo['cc_type'];
        }
        $details['method_title'] = $additionalInfo['method_title'];
        return $details;
    }

    /**
     * @return array
     */
    public function getCustomerAddressInline()
    {
        $customerId = $this->customerSession->getCustomerId();
        $customerData = $this->customer->create()->load($customerId);
        $customerAddress = [];
        foreach ($customerData->getAddresses() as $key => $address) {
            $customerAddress[$key]['label'] = $this->subscriptionHelperData->getSubCustomerAddress($address->getEntityId(), 'inline');
            $customerAddress[$key]['value'] = $address->getEntityId();
        }
        return $customerAddress;
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getUpdateUrl($order_id)
    {
        return $this->getUrl('subscription/order/update', ['order_id' => $order_id]);
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getCancelUrl($order_id)
    {
        return $this->getUrl('subscription/order/cancel', ['order_id' => $order_id]);
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getPauseUrl($order_id)
    {
        return $this->getUrl('subscription/order/pause', ['order_id' => $order_id]);
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getEditUrl($order_id)
    {
        return $this->getUrl('subscription/order/edit', ['order_id' => $order_id]);
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getReactivateUrl($order_id)
    {
        return $this->getUrl('subscription/order/activate', ['order_id' => $order_id]);
    }
}
