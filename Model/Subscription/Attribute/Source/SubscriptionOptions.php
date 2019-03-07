<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Subscription\Attribute\Source;
/**
 * Class SubscriptionOptions
 * @package Wagento\Subscription\Model\Subscription\Attribute\Source
 */
class SubscriptionOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'no', 'label' => __('No')],
                ['value' => 'subscription_only', 'label' => __('Subscription Only')],
                ['value' => 'optional', 'label' => __('Optional')],
            ];
        }

        return $this->_options;
    }
}
