<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Tests\NamingConvention\true\string;
use Wagento\Subscription\Api\Data\SalesSubscriptionInterface;

class SubscriptionSales extends \Magento\Framework\Model\AbstractModel implements SalesSubscriptionInterface
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wagento\Subscription\Model\ResourceModel\SubscriptionSales');
    }

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set id function.
     *
     * @param int|mixed $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get customer id function.
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer id function.
     *
     * @param int $customerId
     * @return mixed|SubscriptionSales
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get subscribe order id.
     *
     * @return mixed
     */
    public function getSubscribeOrderId()
    {
        return $this->getData(self::SUBSCRIBE_ORDER_ID);
    }

    /**
     * Set subscribe order id function.
     *
     * @param int $subscribeOrderId
     * @return mixed|SubscriptionSales
     */
    public function setSubscribeOrderId($subscribeOrderId)
    {
        return $this->setData(self::SUBSCRIBE_ORDER_ID, $subscribeOrderId);
    }

    /**
     * Get status function.
     *
     * @return array|mixed|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set stutus function.
     *
     * @param mixed $status
     * @return mixed|SubscriptionSales
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get last renewed function.
     *
     * @return array|mixed|null
     */
    public function getLastRenewed()
    {
        return $this->getData(self::LAST_RENEWED);
    }

    /**
     * Set last renewed function.
     *
     * @param mixed $lastRenewed
     * @return mixed|SubscriptionSales
     */
    public function setLastRenewed($lastRenewed)
    {
        return $this->setData(self::LAST_RENEWED, $lastRenewed);
    }

    /**
     * Get next renewed function.
     *
     * @return array|mixed|null
     */
    public function getNextRenewed()
    {
        return $this->getData(self::NEXT_RENEWED);
    }

    /**
     * Set next renewed function.
     *
     * @param string $nextRenewed
     * @return mixed|SubscriptionSales
     */
    public function setNextRenewed($nextRenewed)
    {
        return $this->setData(self::NEXT_RENEWED, $nextRenewed);
    }

    /**
     * Get createdat function.
     *
     * @return array|mixed|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set createdat function.
     *
     * @param mixed $createdAt
     * @return mixed|SubscriptionSales
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updatedat function.
     *
     * @return array|mixed|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updatedat function.
     *
     * @param mixed $updatedAt
     * @return mixed|SubscriptionSales
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get storeid function.
     *
     * @return array|mixed|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store id function.
     *
     * @param mixed $storeId
     * @return mixed|SubscriptionSales
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get sub start date function.
     *
     * @return array|mixed|null
     */
    public function getSubStartDate()
    {
        return $this->getData(self::SUB_START_DATE);
    }

    /**
     * Set substart date id.
     *
     * @param mixed $subStartDate
     * @return mixed|SubscriptionSales
     */
    public function setSubStartDate($subStartDate)
    {
        return $this->setData(self::SUB_START_DATE, $subStartDate);
    }

    /**
     * Get sub order itemid function.
     *
     * @return array|mixed|null
     */
    public function getSubOrderItemId()
    {
        return $this->getData(self::SUB_ORDER_ITEM_ID);
    }

    /**
     * Set suborder itemid function.
     *
     * @param mixed $subOrderItemId
     * @return mixed|SubscriptionSales
     */
    public function setSubOrderItemId($subOrderItemId)
    {
        return $this->setData(self::SUB_ORDER_ITEM_ID, $subOrderItemId);
    }

    /**
     * Get howmany function key.
     *
     * @return array|mixed|null
     */
    public function getHowMany()
    {
        return $this->getData(self::HOW_MANY);
    }

    /**
     * Set howmany function key.
     *
     * @param mixed $howmMany
     * @return mixed|SubscriptionSales
     */
    public function setHowMany($howmMany)
    {
        return $this->setData(self::HOW_MANY, $howmMany);
    }

    /**
     * Get billing count function.
     *
     * @return array|mixed|null
     */
    public function getBillingCount()
    {
        return $this->getData(self::BILLING_COUNT);
    }

    /**
     * Set billing count function.
     *
     * @param mixed $billingCount
     * @return mixed|SubscriptionSales
     */
    public function setBillingCount($billingCount)
    {
        return $this->setData(self::BILLING_COUNT, $billingCount);
    }

    /**
     * Get billing address id function.
     *
     * @return array|mixed|null
     */
    public function getBillingAddressId()
    {
        return $this->getData(self::BILLING_ADDRESS_ID);
    }

    /**
     * Set billing address id.
     *
     * @param mixed $billingAddressId
     * @return mixed|SubscriptionSales
     */
    public function setBillingAddressId($billingAddressId)
    {
        return $this->setData(self::BILLING_ADDRESS_ID, $billingAddressId);
    }

    /**
     * Get shipping address id.
     *
     * @return array|mixed|null
     */
    public function getShippingAddressId()
    {
        return $this->getData(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * Set shipping address id.
     *
     * @param mixed $shippingAddressId
     * @return mixed|SubscriptionSales
     */
    public function setShippingAddressId($shippingAddressId)
    {
        return $this->setData(self::SHIPPING_ADDRESS_ID, $shippingAddressId);
    }

    /**
     * Get publichash function.
     *
     * @return array|mixed|null
     */
    public function getPublicHash()
    {
        return $this->getData(self::PUBLIC_HASH);
    }

    /**
     * Set publichash function.
     *
     * @param mixed $publicHash
     * @return mixed|SubscriptionSales
     */
    public function setPublicHash($publicHash)
    {
        return $this->setData(self::PUBLIC_HASH, $publicHash);
    }

    /**
     * Get subname function.
     *
     * @return array|mixed|null
     */
    public function getSubName()
    {
        return $this->getData(self::SUB_NAME);
    }

    /**
     * Set sub name function.
     *
     * @param mixed $subName
     * @return mixed|SubscriptionSales
     */
    public function setSubName($subName)
    {
        return $this->setData(self::SUB_NAME, $subName);
    }

    /**
     * Get sub frequency function.
     *
     * @return array|mixed|null
     */
    public function getSubFrequency()
    {
        return $this->getData(self::SUB_FREQUENCY);
    }

    /**
     * Set sub frequency function.
     *
     * @param mixed $subFrequency
     * @return mixed|SubscriptionSales
     */
    public function setSubFrequency($subFrequency)
    {
        return $this->setData(self::SUB_FREQUENCY, $subFrequency);
    }

    /**
     * Get sub fee function.
     *
     * @return array|mixed|null
     */
    public function getSubFee()
    {
        return $this->getData(self::SUB_FEE);
    }

    /**
     * Set sub fee function.
     *
     * @param mixed $subFee
     * @return mixed|SubscriptionSales
     */
    public function setSubFee($subFee)
    {
        return $this->setData(self::SUB_FEE, $subFee);
    }

    /**
     * Get sub discount function.
     *
     * @return array|mixed|null
     */
    public function getSubDiscount()
    {
        return $this->getData(self::SUB_DISCOUNT);
    }

    /**
     * Set sub discount function.
     *
     * @param mixed $subDiscount
     * @return mixed|SubscriptionSales
     */
    public function setSubDiscount($subDiscount)
    {
        return $this->setData(self::SUB_DISCOUNT, $subDiscount);
    }

    /**
     * Get sub product id function.
     *
     * @return array|mixed|null
     */
    public function getSubProductId()
    {
        return $this->getData(self::SUB_PRODUCT_ID);
    }

    /**
     * Set sub product date function.
     *
     * @param mixed $subProductId
     * @return mixed|SubscriptionSales
     */
    public function setSubProductId($subProductId)
    {
        return $this->setData(self::SUB_PRODUCT_ID, $subProductId);
    }
}
