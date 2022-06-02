<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Plugin;

class GuestCheckout
{
    /**
     * @var \Wagento\Subscription\Helper\Data
     */
    public $dataHelper;

    /**
     * GuestCheckout constructor.
     * @param \Wagento\Subscription\Helper\Data $dataHelper
     */
    public function __construct(\Wagento\Subscription\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Allowed guest checkout function.
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param int|void $result
     * @return int|void
     */
    public function afterIsAllowedGuestCheckout(\Magento\Checkout\Helper\Data $subject, $result)
    {
        $result = $this->dataHelper->getIsGuestCheckout();
        return $result;
    }
}
