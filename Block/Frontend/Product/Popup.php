<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Wagento\Subscription\Helper\Data;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;

class Popup extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var SubscriptionFactory
     */
    public $subscriptionFactory;

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var array|string
     */
    public $productType;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var  array|string
     */
    public $jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * Popup constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Checkout\Model\Cart $cart
     * @param SubscriptionFactory $subscriptionFactory
     * @param ProductFactory $productFactory
     * @param Data $helper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Cart $cart,
        SubscriptionFactory $subscriptionFactory,
        ProductFactory $productFactory,
        Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

        $this->productFactory = $productFactory->create()->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $this->getProductId()])
        ;
        $this->subscriptionFactory = $subscriptionFactory->create()
            ->load($this->returnSubscriptionId($this->productFactory))
        ;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Get subscription name.
     *
     * @return null|string
     */
    public function getSubscriptionName()
    {
        return $this->subscriptionFactory->getName();
    }

    /**
     * Get subscription fee.
     *
     * @return null|float
     */
    public function getSubscriptionFee()
    {
        return $this->subscriptionFactory->getFee();
    }

    /**
     * Get subscription frequency.
     *
     * @return string
     */
    public function getSubscriptionFrequency()
    {
        $frequency = $this->subscriptionFactory->getFrequency();

        return $this->helper->getSubscriptionFrequency($frequency);
    }

    /**
     * Get no of subscription.
     *
     * @return null|float
     */
    public function getSubscriptionHowMany()
    {
        $howMany = $this->subscriptionFactory->getHowMany();
        if (isset($howMany) && 0 != $howMany) {
            return $howMany;
        }

        return null;
    }

    /**
     * Get subscription discount.
     *
     * @return float
     */
    public function getSubscriptionDiscount()
    {
        return $this->subscriptionFactory->getDiscount();
    }

    /**
     * Get subscription id.
     *
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->returnSubscriptionId($this->productFactory);
    }

    /**
     * Get product id.
     *
     * @return int
     */
    public function getProductId()
    {
        $pRegistory = $this->_coreRegistry->registry('product');
        if (isset($pRegistory)) {
            return $this->_coreRegistry->registry('product')->getEntityId();
        }
    }

    /**
     * Get no fo products in cart.
     *
     * @param mixed $product_id
     *
     * @return bool
     */
    public function getProductInCart($product_id)
    {
        $found = 0;
        $items = $this->cart->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getData('product_id') == $product_id && $item->getData('is_subscribed')) {
                $found = 1;

                break;
            }
        }

        return $found;
    }

    /**
     * Enable no of subscription.
     *
     * @return mixed
     */
    public function isEnableHowMany()
    {
        return $this->helper->isSubscriptionEnableHowMany();
    }

    /**
     * No of subscription unit.
     *
     * @return string
     */
    public function getHowManyUnit()
    {
        $frequency = $this->subscriptionFactory->getFrequency();

        return $this->helper->getHowManyUnits($frequency);
    }

    /**
     * Get product type.
     *
     * @return array|string
     */
    public function getProductType()
    {
        return $this->_coreRegistry->registry('product')->getTypeId();
    }

    /**
     * Return subscription id.
     *
     * @param mixed $productCollector
     *
     * @return mixed
     */
    private function returnSubscriptionId($productCollector)
    {
        foreach ($productCollector as $item) {
            return $item->getData('subscription_id');
        }
    }

    /**
     * Get formatted Price.
     *
     * @param mixed $price
     * @return float|string
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
