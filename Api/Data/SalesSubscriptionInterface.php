<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SalesSubscriptionInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case.
     */
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const SUBSCRIBE_ORDER_ID = 'subscribe_order_id';
    public const STATUS = 'status';
    public const LAST_RENEWED = 'last_renewed';
    public const NEXT_RENEWED = 'next_renewed';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const STORE_ID = 'store_id';
    public const SUB_START_DATE = 'sub_start_date';
    public const SUB_ORDER_ITEM_ID = 'sub_order_item_id';
    public const HOW_MANY = 'how_many';
    public const BILLING_COUNT = 'billing_count';
    public const BILLING_ADDRESS_ID = 'billing_address_id';
    public const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    public const PUBLIC_HASH = 'public_hash';
    public const SUB_NAME = 'sub_name';
    public const SUB_FREQUENCY = 'sub_frequency';
    public const SUB_FEE = 'sub_fee';
    public const SUB_DISCOUNT = 'sub_discount';
    public const SUB_PRODUCT_ID = 'sub_product_id';

    /**
     * Get Id.
     *
     * @return null|int
     */
    public function getId();

    /**
     * Set Id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get customer id.
     *
     * @return mixed
     */
    public function getCustomerId();

    /**
     * Set customer id.
     *
     * @param mixed $customerId
     *
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * Get subscribeorder id.
     *
     * @return mixed
     */
    public function getSubscribeOrderId();

    /**
     * Set subscribeorder id.
     *
     * @param mixed $subscribeOrderId
     *
     * @return mixed
     */
    public function setSubscribeOrderId($subscribeOrderId);

    /**
     * Get status.
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Set status.
     *
     * @param mixed $status
     *
     * @return mixed
     */
    public function setStatus($status);

    /**
     * Get last renewed.
     *
     * @return mixed
     */
    public function getLastRenewed();

    /**
     * Set last renewed.
     *
     * @param mixed $lastRenewed
     *
     * @return mixed
     */
    public function setLastRenewed($lastRenewed);

    /**
     * Get next renewed.
     *
     * @return mixed
     */
    public function getNextRenewed();

    /**
     * Set next renewed.
     *
     * @param mixed $nextRenewed
     *
     * @return mixed
     */
    public function setNextRenewed($nextRenewed);

    /**
     * Get createdat.
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Set created at.
     *
     * @param mixed $createdAt
     *
     * @return mixed
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at.
     *
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * Set updated at.
     *
     * @param mixed $updatedAt
     *
     * @return mixed
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get store id.
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Set store id.
     *
     * @param mixed $storeId
     *
     * @return mixed
     */
    public function setStoreId($storeId);

    /**
     * Get substart date.
     *
     * @return mixed
     */
    public function getSubStartDate();

    /**
     * Set substart date.
     *
     * @param mixed $subStartDate
     *
     * @return mixed
     */
    public function setSubStartDate($subStartDate);

    /**
     * Get suborder item id.
     *
     * @return mixed
     */
    public function getSubOrderItemId();

    /**
     * Set suborder item id.
     *
     * @param mixed $subOrderItemId
     *
     * @return mixed
     */
    public function setSubOrderItemId($subOrderItemId);

    /**
     * Get how many.
     *
     * @return mixed
     */
    public function getHowMany();

    /**
     * Set how many.
     *
     * @param mixed $howmMany
     *
     * @return mixed
     */
    public function setHowMany($howmMany);

    /**
     * Get billing count.
     *
     * @return mixed
     */
    public function getBillingCount();

    /**
     * Set billing count.
     *
     * @param mixed $billingCount
     *
     * @return mixed
     */
    public function setBillingCount($billingCount);

    /**
     * Get billing address id.
     *
     * @return mixed
     */
    public function getBillingAddressId();

    /**
     * Set billing address id.
     *
     * @param mixed $billingAddressId
     *
     * @return mixed
     */
    public function setBillingAddressId($billingAddressId);

    /**
     * Get shipping address id.
     *
     * @return mixed
     */
    public function getShippingAddressId();

    /**
     * Set shipping address id.
     *
     * @param mixed $shippingAddressId
     *
     * @return mixed
     */
    public function setShippingAddressId($shippingAddressId);

    /**
     * Get public hash.
     *
     * @return mixed
     */
    public function getPublicHash();

    /**
     * Set public hash.
     *
     * @param mixed $publicHash
     *
     * @return mixed
     */
    public function setPublicHash($publicHash);

    /**
     * Get sub name.
     *
     * @return mixed
     */
    public function getSubName();

    /**
     * Set subname.
     *
     * @param mixed $subName
     *
     * @return mixed
     */
    public function setSubName($subName);

    /**
     * Get sub frequency.
     *
     * @return mixed
     */
    public function getSubFrequency();

    /**
     * Set sub frequency.
     *
     * @param mixed $subFrequency
     *
     * @return mixed
     */
    public function setSubFrequency($subFrequency);

    /**
     * Get subfee.
     *
     * @return mixed
     */
    public function getSubFee();

    /**
     * Set subfee.
     *
     * @param mixed $subFee
     *
     * @return mixed
     */
    public function setSubFee($subFee);

    /**
     * Get sub discount.
     *
     * @return mixed
     */
    public function getSubDiscount();

    /**
     * Set sub discount.
     *
     * @param mixed $subDiscount
     *
     * @return mixed
     */
    public function setSubDiscount($subDiscount);

    /**
     * Get sub product id.
     *
     * @return mixed
     */
    public function getSubProductId();

    /**
     * Set sub product id.
     *
     * @param mixed $subProductId
     *
     * @return mixed
     */
    public function setSubProductId($subProductId);
}
