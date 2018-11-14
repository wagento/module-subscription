<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'subscription_id';

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Wagento\Subscription\Model\Subscription', 'Wagento\Subscription\Model\ResourceModel\Subscription');
    }
}
