<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Subscription;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Convert\ConvertArray;
use Wagento\Subscription\Api\Data\SubscriptionInterface;

class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert tree data object to a flat array.
     *
     * @param SubscriptionInterface $subscription
     * @return array
     */
    public function toFlatArray(SubscriptionInterface $subscription)
    {
        $flatArray = $this->extensibleDataObjectConverter
            ->toNestedArray($subscription, [], \Wagento\Subscription\Api\Data\SubscriptionInterface::class)
        ;

        return ConvertArray::toFlatArray($flatArray);
    }
}
