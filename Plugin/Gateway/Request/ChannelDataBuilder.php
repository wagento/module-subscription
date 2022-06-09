<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Plugin\Gateway\Request;

class ChannelDataBuilder
{
    /**
     * After build function.
     *
     * @param \PayPal\Braintree\Gateway\Request\ChannelDataBuilder $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterBuild(\PayPal\Braintree\Gateway\Request\ChannelDataBuilder $subject, $result)
    {
        $result['channel'] = 'Wagento_subscription';

        return $result;
    }
}
