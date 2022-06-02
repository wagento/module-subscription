<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Quote\Address\Total;

class InitialFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator|null
     */
    protected $quoteValidator = null;
    /**
     * @var \Wagento\Subscription\Helper\Product
     */
    protected $subProductHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * InitialFee constructor.
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {

        $this->quoteValidator = $quoteValidator;
        $this->subProductHelper = $subProductHelper;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Quote collect function.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $initialFee = $this->getInitialFeeValue($items);
        $total->addTotalAmount('initialfee', $initialFee);
        $total->addBaseTotalAmount('initialfee', $initialFee);
        $quote->setInitialFee($initialFee);
        return $this;
    }

    /**
     * Quote fecth function.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $code = 'initialfee';
        $initialFee = ($quote->getInitialFee()) ? $quote->getInitialFee() : 0;
        $result = [
            'code' => $code,
            'title' => 'Subscription Initial Fee',
            'value' => $initialFee
        ];
        return $result;
    }

    /**
     * Get quote initial fee value function.
     *
     * @param mixed $items
     * @return float|null
     */
    protected function getInitialFeeValue($items)
    {
        $initialfee = 0.0000;
        foreach ($items as $key => $item) {
            $productId = $item->getProductId();
            $isSubscribed = $item->getIsSubscribed();
            if ($isSubscribed != 0) {
                $initialfee += $this->subProductHelper->getInitialFee($productId);
            }
        }
        return $initialfee;
    }
}
