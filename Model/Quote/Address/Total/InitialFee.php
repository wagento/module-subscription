<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Quote\Address\Total;

use Magento\Checkout\Model\Cart;

class InitialFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Quote\Model\QuoteValidator|null
     */
    protected $quoteValidator = null;
    /**
     * @var Cart
     */
    protected $cart;
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
     * @param Cart $cart
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        Cart $cart,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {

        $this->quoteValidator = $quoteValidator;
        $this->cart = $cart;
        $this->subProductHelper = $subProductHelper;
        $this->_priceCurrency = $priceCurrency;
    }

    /**
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
        $items = $this->cart->getItems();
        $initialFee = $this->getInitialFeeValue($items);
        $total->addTotalAmount('initialfee', $initialFee);
        $total->addBaseTotalAmount('initialfee', $initialFee);
        $quote->setInitialFee($initialFee);
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $items = $this->cart->getItems();
        $initialfee = $this->getInitialFeeValue($items);

        return [
            'code' => 'initialfee',
            'title' => 'Subscription Initial Fee',
            'value' => $initialfee
        ];
    }

    /**
     * @param $items
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
