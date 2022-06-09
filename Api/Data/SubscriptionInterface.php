<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SubscriptionInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case.
     */
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const NAME = 'name';
    public const FREQUENCY = 'frequency';
    public const FEE = 'fee';
    public const DATE_START = 'date_start';
    public const DATE_END = 'date_end';
    public const HOW_MANY = 'how_many';
    public const DISCOUNT = 'discount';

    /**
     * Get subscription id.
     *
     * @return null|int
     */
    public function getSubscriptionId();

    /**
     * Set subscription id.
     *
     * @param int $subscriptionId
     *
     * @return $this
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * Get name.
     *
     * @return null|string
     */
    public function getName();

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get frequency.
     *
     * @return null|int
     */
    public function getFrequency();

    /**
     * Set frequency.
     *
     * @param int $frequency
     *
     * @return $this
     */
    public function setFrequency($frequency);

    /**
     * Get fee.
     *
     * @return null|float
     */
    public function getFee();

    /**
     * Set fee.
     *
     * @param float $fee
     *
     * @return $this
     */
    public function setFee($fee);

    /**
     * Get Start Date.
     *
     * @return $this
     */
    public function getDateStart();

    /**
     * Set Start Date.
     *
     * @param mixed $dateStart
     *
     * @return $this
     */
    public function setDateStart($dateStart);

    /**
     * Get End Date.
     *
     * @return $this
     */
    public function getDateEnd();

    /**
     * Set End Date.
     *
     * @param mixed $dateEnd
     *
     * @return $this
     */
    public function setDateEnd($dateEnd);

    /**
     * Get How Many.
     *
     * @return null|float
     */
    public function getHoWMany();

    /**
     * Set How Many.
     *
     * @param float $howMany
     *
     * @return $this
     */
    public function setHowMany($howMany);

    /**
     * Get Discount.
     *
     * @return float
     */
    public function getDiscount();

    /**
     * Set Discount.
     *
     * @param float $discount
     *
     * @return $this
     */
    public function setDiscount($discount);
}
