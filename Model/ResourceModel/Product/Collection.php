<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\ResourceModel\Product;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Production collection _construct
     */
    protected function _construct()
    {
        $this->_init(
            \Wagento\Subscription\Model\Product::class,
            \Wagento\Subscription\Model\ResourceModel\Product::class
        );
    }
}
