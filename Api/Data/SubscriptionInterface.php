<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SubscriptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const SUBSCRIPTION_ID = 'subscription_id';
    const NAME = 'name';
    const FREQUENCY = 'frequency';
    const FEE = 'fee';
    const DATE_START = 'date_start';
    const DATE_END = 'date_end';
    const HOW_MANY = 'how_many';
    const DISCOUNT = 'discount';
    /**#@-*/

    /**
     * Get subscription id
     *
     * @return int|null
     */
    public function getSubscriptionId();

    /**
     * Set subscription id
     *
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get frequency
     *
     * @return int|null
     */
    public function getFrequency();

    /**
     * Set frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency);

    /**
     * Get fee
     *
     * @return float|null
     */
    public function getFee();

    /**
     * Set fee
     *
     * @param float $fee
     * @return $this
     */
    public function setFee($fee);

    /**
     * Get Start Date
     *
     * @return $this
     */
    public function getDateStart();

    /**
     * Set Start Date
     *
     * @param $dateStart
     * @return $this
     */
    public function setDateStart($dateStart);

    /**
     * Get End Date
     *
     * @return $this
     */
    public function getDateEnd();

    /**
     * Set End Date
     *
     * @param $dateEnd
     * @return $this
     */
    public function setDateEnd($dateEnd);

    /**
     * Get How Many
     *
     * @return float|null
     */
    public function getHoWMany();

    /**
     * Set How Many
     *
     * @param float $howMany
     * @return $this
     */
    public function setHowMany($howMany);

    /**
     * Get Discount
     *
     * @return float
     */
    public function getDiscount();

    /**
     * Set Discount
     *
     * @param float $discount
     * @return $this
     */
    public function setDiscount($discount);
}
