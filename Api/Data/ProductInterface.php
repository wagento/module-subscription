<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ProductInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';
    public const SUBSCRIPTION_ID = 'subscription_id';
    public const PRODUCT_ID = 'product_id';

    /**
     * Get product subscription id.
     *
     * @return null|int
     */
    public function getEntityId();

    /**
     * Set product subscription id.
     *
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId);

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
     * Get product id.
     *
     * @return null|int
     */
    public function getProductId();

    /**
     * Set product id.
     *
     * @param mixed $productId
     *
     * @return mixed
     */
    public function setProductId($productId);
}
