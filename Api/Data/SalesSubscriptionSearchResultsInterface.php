<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SalesSubscriptionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscription list.
     *
     * @return \Wagento\Subscription\Api\Data\SalesSubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set subscription list.
     *
     * @param \Wagento\Subscription\Api\Data\SalesSubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
