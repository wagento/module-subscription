<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Plugin\Gateway\Request;

/**
 * Class BnCodeDataBuilder
 */
class ChannelDataBuilder
{
    /**
     * @param \PayPal\Braintree\Gateway\Request\ChannelDataBuilder $subject
     * @param $result
     * @return mixed
     */
    public function afterBuild(\PayPal\Braintree\Gateway\Request\ChannelDataBuilder $subject, $result)
    {
        $result['channel'] = 'Wagento_subscription';
        return $result;
    }
}
