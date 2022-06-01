<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Subscription\Attribute\Source;
/**
 * Class SubscriptionList
 */
class SubscriptionList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var SubscriptionList
     */
    private $subscription;

    /**
     * SubscriptionList constructor.
     * @param \Wagento\Subscription\Model\SubscriptionFactory $subscription
     */
    public function __construct(
        \Wagento\Subscription\Model\SubscriptionFactory $subscription
    ) {

        $this->subscription = $subscription->create();
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $collection = $this->subscription->getCollection();
        $result = [];
        foreach ($collection as $item) {
            $result[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $result;
    }
}
