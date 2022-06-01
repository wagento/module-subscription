<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Wagento\Subscription\Api\Data\SalesSubscriptionInterface;

/**
 * Class SubscriptionSales
 */
class SubscriptionSales extends \Magento\Framework\Model\AbstractModel implements SalesSubscriptionInterface
{
    /**
     * Initialize resource model.
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
     * @param int|mixed $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return mixed
     */
    public function getSubscribeOrderId()
    {
        return $this->getData(self::SUBSCRIBE_ORDER_ID);
    }

    /**
     * @param $subscribeOrderId
     * @return mixed
     */
    public function setSubscribeOrderId($subscribeOrderId)
    {
        return $this->setData(self::SUBSCRIBE_ORDER_ID, $subscribeOrderId);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return mixed
     */
    public function getLastRenewed()
    {
        return $this->getData(self::LAST_RENEWED);
    }

    /**
     * @param $lastRenewed
     * @return mixed
     */
    public function setLastRenewed($lastRenewed)
    {
        return $this->setData(self::LAST_RENEWED, $lastRenewed);
    }

    /**
     * @return mixed
     */
    public function getNextRenewed()
    {
        return $this->getData(self::NEXT_RENEWED);
    }

    /**
     * @param $nextRenewed
     * @return mixed
     */
    public function setNextRenewed($nextRenewed)
    {
        return $this->setData(self::NEXT_RENEWED, $nextRenewed);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return mixed
     */
    public function getSubStartDate()
    {
        return $this->getData(self::SUB_START_DATE);
    }

    /**
     * @param $subStartDate
     * @return mixed
     */
    public function setSubStartDate($subStartDate)
    {
        return $this->setData(self::SUB_START_DATE, $subStartDate);
    }

    /**
     * @return mixed
     */
    public function getSubOrderItemId()
    {
        return $this->getData(self::SUB_ORDER_ITEM_ID);
    }

    /**
     * @param $subOrderItemId
     * @return mixed
     */
    public function setSubOrderItemId($subOrderItemId)
    {
        return $this->setData(self::SUB_ORDER_ITEM_ID, $subOrderItemId);
    }

    /**
     * @return mixed
     */
    public function getHowMany()
    {
        return $this->getData(self::HOW_MANY);
    }

    /**
     * @param $howmMany
     * @return mixed
     */
    public function setHowMany($howmMany)
    {
        return $this->setData(self::HOW_MANY, $howmMany);
    }

    /**
     * @return mixed
     */
    public function getBillingCount()
    {
        return $this->getData(self::BILLING_COUNT);
    }

    /**
     * @param $billingCount
     * @return mixed
     */
    public function setBillingCount($billingCount)
    {
        return $this->setData(self::BILLING_COUNT, $billingCount);
    }

    /**
     * @return mixed
     */
    public function getBillingAddressId()
    {
        return $this->getData(self::BILLING_ADDRESS_ID);
    }

    /**
     * @param $billingAddressId
     * @return mixed
     */
    public function setBillingAddressId($billingAddressId)
    {
        return $this->setData(self::BILLING_ADDRESS_ID, $billingAddressId);
    }

    /**
     * @return mixed
     */
    public function getShippingAddressId()
    {
        return $this->getData(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * @param $shippingAddressId
     * @return mixed
     */
    public function setShippingAddressId($shippingAddressId)
    {
        return $this->setData(self::SHIPPING_ADDRESS_ID, $shippingAddressId);
    }

    /**
     * @return mixed
     */
    public function getPublicHash()
    {
        return $this->getData(self::PUBLIC_HASH);
    }

    /**
     * @param $publicHash
     * @return mixed
     */
    public function setPublicHash($publicHash)
    {
        return $this->setData(self::PUBLIC_HASH, $publicHash);
    }

    /**
     * @return mixed
     */
    public function getSubName()
    {
        return $this->getData(self::SUB_NAME);
    }

    /**
     * @param $subName
     * @return mixed
     */
    public function setSubName($subName)
    {
        return $this->setData(self::SUB_NAME, $subName);
    }

    /**
     * @return mixed
     */
    public function getSubFrequency()
    {
        return $this->getData(self::SUB_FREQUENCY);
    }

    /**
     * @param $subFrequency
     * @return mixed
     */
    public function setSubFrequency($subFrequency)
    {
        return $this->setData(self::SUB_FREQUENCY, $subFrequency);
    }

    /**
     * @return mixed
     */
    public function getSubFee()
    {
        return $this->getData(self::SUB_FEE);
    }

    /**
     * @param $subFee
     * @return mixed
     */
    public function setSubFee($subFee)
    {
        return $this->setData(self::SUB_FEE,$subFee);
    }

    /**
     * @return mixed
     */
    public function getSubDiscount()
    {
        return $this->getData(self::SUB_DISCOUNT);
    }

    /**
     * @param $subDiscount
     * @return mixed
     */
    public function setSubDiscount($subDiscount)
    {
        return $this->setData(self::SUB_DISCOUNT, $subDiscount);
    }

    /**
     * @return mixed
     */
    public function getSubProductId()
    {
        return $this->getData(self::SUB_PRODUCT_ID);
    }

    /**
     * @param $subProductId
     * @return mixed
     */
    public function setSubProductId($subProductId)
    {
        return $this->setData(self::SUB_PRODUCT_ID, $subProductId);
    }
}
