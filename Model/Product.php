<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Wagento\Subscription\Api\Data\ProductInterface;

class Product extends AbstractExtensibleModel implements ProductInterface
{
    /**
     * Construct function.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Wagento\Subscription\Model\ResourceModel\Product::class);
    }

    /**
     * Get subscription id.
     *
     * @return null|int
     */
    public function getSubscriptionId()
    {
        return $this->_getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set subscription id.
     *
     * @param int $subscriptionId
     *
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Get product id.
     *
     * @return null|int
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * Set product id.
     *
     * @param mixed $productId
     *
     * @return mixed
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
