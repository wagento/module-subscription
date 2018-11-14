<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

class AddInitialFeeToOrder implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * AddInitialFeeToOrder constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
    
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $order->setOrderComment($quote->getOrderComment());
        $quote = $observer->getQuote();
        $order->setInitialFee($quote->getInitialFee());
        return $this;
    }
}
