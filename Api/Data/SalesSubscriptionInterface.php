<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SalesSubscriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const SUBSCRIBE_ORDER_ID = 'subscribe_order_id';
    const STATUS = 'status';
    const LAST_RENEWED = 'last_renewed';
    const NEXT_RENEWED = 'next_renewed';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const SUB_START_DATE = 'sub_start_date';
    const SUB_ORDER_ITEM_ID = 'sub_order_item_id';
    const HOW_MANY = 'how_many';
    const BILLING_COUNT = 'billing_count';
    const BILLING_ADDRESS_ID = 'billing_address_id';
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    const PUBLIC_HASH = 'public_hash';
    const SUB_NAME = 'sub_name';
    const SUB_FREQUENCY = 'sub_frequency';
    const SUB_FEE = 'sub_fee';
    const SUB_DISCOUNT = 'sub_discount';
    const SUB_PRODUCT_ID = 'sub_product_id';
    
    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @return mixed
     */
    public function getSubscribeOrderId();

    /**
     * @param $subscribeOrderId
     * @return mixed
     */
    public function setSubscribeOrderId($subscribeOrderId);

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getLastRenewed();

    /**
     * @param $lastRenewed
     * @return mixed
     */
    public function setLastRenewed($lastRenewed);

    /**
     * @return mixed
     */
    public function getNextRenewed();

    /**
     * @param $nextRenewed
     * @return mixed
     */
    public function setNextRenewed($nextRenewed);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return mixed
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return mixed
     */
    public function getStoreId();

    /**
     * @param $storeId
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * @return mixed
     */
    public function getSubStartDate();

    /**
     * @param $subStartDate
     * @return mixed
     */
    public function setSubStartDate($subStartDate);

    /**
     * @return mixed
     */
    public function getSubOrderItemId();

    /**
     * @param $subOrderItemId
     * @return mixed
     */
    public function setSubOrderItemId($subOrderItemId);

    /**
     * @return mixed
     */
    public function getHowMany();

    /**
     * @param $howmMany
     * @return mixed
     */
    public function setHowMany($howmMany);

    /**
     * @return mixed
     */
    public function getBillingCount();

    /**
     * @param $billingCount
     * @return mixed
     */
    public function setBillingCount($billingCount);

    /**
     * @return mixed
     */
    public function getBillingAddressId();

    /**
     * @param $billingAddressId
     * @return mixed
     */
    public function setBillingAddressId($billingAddressId);

    /**
     * @return mixed
     */
    public function getShippingAddressId();

    /**
     * @param $shippingAddressId
     * @return mixed
     */
    public function setShippingAddressId($shippingAddressId);

    /**
     * @return mixed
     */
    public function getPublicHash();

    /**
     * @param $publicHash
     * @return mixed
     */
    public function setPublicHash($publicHash);

    /**
     * @return mixed
     */
    public function getSubName();

    /**
     * @param $subName
     * @return mixed
     */
    public function setSubName($subName);

    /**
     * @return mixed
     */
    public function getSubFrequency();

    /**
     * @param $subFrequency
     * @return mixed
     */
    public function setSubFrequency($subFrequency);

    /**
     * @return mixed
     */
    public function getSubFee();
    /**
     * @param $subFee
     * @return mixed
     */
    public function setSubFee($subFee);

    /**
     * @return mixed
     */
    public function getSubDiscount();

    /**
     * @param $subDiscount
     * @return mixed
     */
    public function setSubDiscount($subDiscount);

    /**
     * @return mixed
     */
    public function getSubProductId();

    /**
     * @param $subProductId
     * @return mixed
     */
    public function setSubProductId($subProductId);
}
