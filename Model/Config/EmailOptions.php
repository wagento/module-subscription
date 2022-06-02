<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Config;

class EmailOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Option label array function.
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('--- Please Select ---')],
            ['value' => 1, 'label' => __('Reminder Email')],
            ['value' => 2, 'label' => __('Billing Failed Email')],
            ['value' => 3, 'label' => __('Payment Failed Email')],
            ['value' => 4, 'label' => __('Status Change by Admin → Email to Customer')],
            ['value' => 5, 'label' => __('Status Change by Customer → Email to Merchant')],
        ];
    }
}
