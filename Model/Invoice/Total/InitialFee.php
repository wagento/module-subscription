<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class InitialFee extends AbstractTotal
{
    /**
     * Invoice collect function.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setInitialFee(0);
        $amount = $invoice->getOrder()->getInitialFee();
        $invoice->setInitialFee($amount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getInitialFee());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getInitialFee());
        return $this;
    }
}
