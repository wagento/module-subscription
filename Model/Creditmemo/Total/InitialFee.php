<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

/**
 * Class InitialFee
 */
class InitialFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $amount = $creditmemo->getOrder()->getInitialFee();
        $creditmemo->setInitialFee($amount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getInitialFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getInitialFee());
        return $this;
    }
}
