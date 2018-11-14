<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SubscriptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscription list.
     *
     * @return \Wagento\Subscription\Api\Data\SubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set subscription list.
     *
     * @param \Wagento\Subscription\Api\Data\SubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
