<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\ResourceModel\SubscriptionSales;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Wagento\Subscription\Model\SubscriptionSales::class',
            'Wagento\Subscription\Model\ResourceModel\SubscriptionSales::class'
        );
    }
}
