<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class StatusOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Cancel')
            ],
            [
                'value' => 1,
                'label' => __('Activate')
            ],
            [
                'value' => 2,
                'label' => __('Pause')
            ],
            [
                'value' => 3,
                'label' => __('Completed')
            ]
        ];
    }
}
