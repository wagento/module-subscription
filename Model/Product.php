<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Wagento\Subscription\Api\Data\ProductInterface;

/**
 * Class Product
 */
class Product extends AbstractExtensibleModel implements ProductInterface
{

    /**
     * return @void
     */
    public function _construct()
    {
        $this->_init(\Wagento\Subscription\Model\ResourceModel\Product::class);
    }

    /**
     * Get subscription id
     *
     * @return int|null
     */
    public function getSubscriptionId()
    {
        return $this->_getData(self::SUBSCRIPTION_ID);
    }

    /**
     * Set subscription id
     *
     * @param int $subscriptionId
     * @return $this
     */
    public function setSubscriptionId($subscriptionId)
    {
        return $this->setData(self::SUBSCRIPTION_ID, $subscriptionId);
    }

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * Set product id
     *
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
