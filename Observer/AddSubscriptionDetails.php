<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Wagento\Subscription\Helper\Data;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;
use Wagento\Subscription\Model\SubscriptionService;

class AddSubscriptionDetails implements ObserverInterface
{
    /**
     * @var \Wagento\Subscription\Model\SubscriptionSalesFactory
     */
    protected $subscriptionSales;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $subHelper;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    /**
     * AddSubscriptionDetails constructor.
     * @param \Wagento\Subscription\Model\SubscriptionSalesFactory $subscriptionSales
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Data $subHelper
     * @param SubscriptionFactory $subscriptionFactory
     * @param ProductFactory $productFactory
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(
        \Wagento\Subscription\Model\SubscriptionSalesFactory $subscriptionSales,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $subHelper,
        SubscriptionFactory $subscriptionFactory,
        ProductFactory $productFactory,
        SubscriptionService $subscriptionService
    ) {
    
        $this->subscriptionSales = $subscriptionSales;
        $this->logger = $logger;
        $this->order = $order;
        $this->dateFactory = $dateFactory;
        $this->storeManager = $storeManager;
        $this->subHelper = $subHelper;
        $this->productFactory = $productFactory->create();
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        } else {
            $storeId = $this->storeManager->getStore()->getId();
            $orderData = $this->order->create()->load($orderIds[0]);
            $shippingAddressId = $orderData->getShippingAddressId();

            if (!$shippingAddressId) {
                $selectedShippingId = null;
            } else {
                $selectedShippingId = $this->subHelper->getSelectedId($shippingAddressId);
            }
            $billingAddressId = $orderData->getBillingAddressId();
            if (!$billingAddressId) {
                $billingAddressId = $shippingAddressId;
            }
            $selectedBillingId = $this->subHelper->getSelectedId($billingAddressId);
            $orderItems = $orderData->getAllVisibleItems();
            $date = $this->dateFactory->create()->gmtDate();
            foreach ($orderItems as $key => $item) {
                if ($item->getIsSubscribed() == 1) {
                    $model = $this->subscriptionSales->create();
                    $orderId[$key] = $item->getItemId();
                    $productId[$key] = $item->getProductId();
                    $productAdditionalOptions[$key] = $item->getProductOptions();
                    $getLabel = $productAdditionalOptions[$key]['additional_options'][2]['label'];
                    if ($getLabel == 'Subscription Cycle') {
                        $howManyValue = $productAdditionalOptions[$key]['additional_options'][2]['value'];
                        $howManyNumber = preg_replace("/[^0-9]/", '', $howManyValue);
                    } else {
                        $howManyNumber = null;
                    }
                    $productCollection = $this->productFactory->getCollection()->addFieldToFilter('product_id', ['eq' => $productId[$key]]);
                    $subscriptionData = $this->subscriptionFactory->load($this->returnSubscriptionId($productCollection));
                    $subscriptionName = $subscriptionData->getName();
                    $subscriptionFrequency = $subscriptionData->getFrequency();
                    $subscriptionFee = $subscriptionData->getFee();
                    $subscriptionDiscount = $subscriptionData->getDiscount();
                    $nextRun = $this->subscriptionService->calculateNextRun($subscriptionFrequency);
                    $model->setCustomerId($orderData->getCustomerId());
                    $model->setSubscribeOrderId($orderIds[0]);
                    $model->setStatus(1);
                    $model->setLastRenewed($date);
                    $model->setNextRenewed($nextRun);
                    $model->setCreatedAt($date);
                    $model->setUpdatedAt($date);
                    $model->setStoreId($storeId);
                    $model->setSubStartDate($date);
                    $model->setSubOrderItemId($orderId[$key]);
                    $model->setSubDiscount($subscriptionDiscount);
                    $model->setSubProductId($productId[$key]);
                    $model->setHowMany($howManyNumber);
                    $model->setBillingAddressId($selectedBillingId);
                    $model->setShippingAddressId($selectedShippingId);
                    $model->setSubName($subscriptionName);
                    $model->setSubFrequency($subscriptionFrequency);
                    $model->setSubFee($subscriptionFee);
                    $model->setSubShippingType(null);
                    $model->setSubShippingRule(null);
                    $model->save();
                } else {
                    continue;
                }
            }
        }
    }

    /**
     * @param $productCollector
     * @return mixed
     */
    private function returnSubscriptionId($productCollector)
    {
        foreach ($productCollector as $item) {
            return $item->getData('subscription_id');
        }
    }
}
