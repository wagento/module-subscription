<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class CustomerDetails extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * @var \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\Collection
     */
    public $collection;

    /**
     * @var string
     */
    protected $_template = 'sales/subscription/customer_details.phtml';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Block\Items\AbstractItems
     */
    protected $abstractItems;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $salesOrder;

    /**
     * @var \Wagento\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * CustomerDetails constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Block\Items\AbstractItems $abstractItems
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Wagento\Subscription\Helper\Data $subHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\OrderRepository $salesOrder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory $collectionFactory,
        \Magento\Sales\Block\Items\AbstractItems $abstractItems,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Wagento\Subscription\Helper\Data $subHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\OrderRepository $salesOrder,
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->_resource = $resource;
        $this->_storeManager = $context->getStoreManager();
        $this->abstractItems = $abstractItems;
        $this->addressRenderer = $addressRenderer;
        $this->salesOrder = $salesOrder;
        $this->helper = $subHelper;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get subscription details function.
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSubscriptionDetail()
    {
        $connection = $this->_resource->getConnection();
        $customerTable = $connection->getTableName('customer_entity');
        $salesOrderItemTable = $connection->getTableName('sales_order_item');
        $wagentoSubProductTable = $connection->getTableName('wagento_subscription_products');

        $id = $this->getRequest()->getParam('id');
        $this->collection->addFieldToFilter('id', ['eq' => $id]);

        $this->collection->getSelect()->join(
            $salesOrderItemTable.' as soi',
            'main_table.sub_order_item_id = soi.item_id && soi.is_subscribed = 1',
            ['*', 'created_at as order_created_at', 'updated_at as order_updated_at']
        );

        $this->collection->getSelect()->join(
            $customerTable.' as customer',
            'main_table.customer_id = customer.entity_id',
            ['firstname', 'lastname', 'email']
        )
            ->columns(new \Zend_Db_Expr("CONCAT(`customer`.`firstname`, ' ',`customer`.`lastname`) AS customer_name"))
        ;

        $this->collection->getSelect()->join(
            $wagentoSubProductTable.' as wsp',
            'soi.product_id = wsp.product_id',
            ['subscription_id']
        );

        return $this->collection->getFirstItem();
    }

    /**
     * Get order store name.
     *
     * @param mixed $storeId
     *
     * @return null|string
     */
    public function getSubscriptionStoreName($storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];

        return implode('<br/>', $name);
    }

    /**
     * Check if is single store mode.
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Get timezone for store.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getTimezoneForStore($store)
    {
        return $this->_localeDate->getConfigTimezone(
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get store function.
     *
     * @param mixed $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore($storeId)
    {
        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        }

        return $this->_storeManager->getStore();
    }

    /**
     * Get URL to edit the customer.
     *
     * @param mixed $customerId
     *
     * @return string
     */
    public function getCustomerViewUrl($customerId)
    {
        if (!$customerId) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $customerId]);
    }

    /**
     * Show tab function.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is hidden function.
     *
     * @return false
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Subscription Info');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Subscription Info');
    }

    /**
     * Get shipping address function.
     *
     * @param mixed $orderId
     * @param mixed $shippingAddressId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return null|\Magento\Framework\Phrase|mixed|string
     */
    public function getShippingAddress($orderId, $shippingAddressId)
    {
        $_order = $this->salesOrder->get($orderId);
        if (isset($shippingAddressId)) {
            return $this->helper->getSubCustomerAddress($shippingAddressId, 'html');
        }
        $shipingAddress = $_order->getShippingAddress();
        if (isset($shipingAddress)) {
            return $this->addressRenderer->format($shipingAddress, 'html');
        }

        return __('N/A');
    }

    /**
     * Get billing address function.
     *
     * @param mixed $orderId
     * @param mixed $billingAddressId
     * @return mixed|string|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBillingAddress($orderId, $billingAddressId)
    {
        $_order = $this->salesOrder->get($orderId);
        if (isset($billingAddressId)) {
            return $this->helper->getSubCustomerAddress($billingAddressId, 'html');
        }
        $billingAddress = $_order->getBillingAddress();

        return $this->addressRenderer->format($billingAddress, 'html');
    }

    /**
     * Get payment method function.
     *
     * @param mixed $orderId
     * @param mixed $publicHash
     * @param mixed $customerId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function getPaymentMethod($orderId, $publicHash, $customerId)
    {
        if (isset($publicHash)) {
            return $this->helper->getCard($customerId, $publicHash);
        }
        $_order = $this->salesOrder->get($orderId);
        $details = [];
        $additionalInfo = $_order->getPayment()->getAdditionalInformation();
        if (isset($additionalInfo['cc_number'], $additionalInfo['cc_type'])) {
            $details['cc_number'] = $additionalInfo['cc_number'];
            $details['cc_type'] = $additionalInfo['cc_type'];
        }
        $details['method_title'] = $additionalInfo['method_title'];

        return $details;
    }

    /**
     * Get shipping method function.
     *
     * @param mixed $orderId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return null|string
     */
    public function getShippingMethod($orderId)
    {
        $_order = $this->salesOrder->get($orderId);

        return $_order->getShippingDescription();
    }

    /**
     * Subscription status function.
     *
     * @param bool $status
     * @return mixed
     */
    public function getSubscriptionStatus($status)
    {
        return $this->helper->getSubscriptionStatus($status);
    }

    /**
     * Subscription frequency function.
     *
     * @param mixed $subFrequency
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionFrequency($subFrequency)
    {
        return $this->helper->getSubscriptionFrequency($subFrequency);
    }

    /**
     * Get no of units.
     *
     * @param int $subFrequency
     *
     * @return string
     */
    public function getHowManyUnits($subFrequency)
    {
        return $this->helper->getHowManyUnits($subFrequency);
    }

    /**
     * Get formatted Price.
     *
     * @param mixed $price
     * @return float|string
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
