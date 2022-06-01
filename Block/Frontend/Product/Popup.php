<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;
use Wagento\Subscription\Helper\Data;

/**
 * Class Popup
 */
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
     * @var
     */
    public $jsonEncoder;

    /**
     * Popup constructor.
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
            ->addFieldToFilter('product_id', ['eq' => $this->getProductId()]);
        $this->subscriptionFactory = $subscriptionFactory->create()
            ->load($this->returnSubscriptionId($this->productFactory));
        $this->cart = $cart;
        $this->helper = $helper;
    }

    /**
     * @return null|string
     */
    public function getSubscriptionName()
    {
        return $this->subscriptionFactory->getName();
    }

    /**
     * @return float|null
     */
    public function getSubscriptionFee()
    {
        return $this->subscriptionFactory->getFee();
    }

    /**
     * @return string
     */
    public function getSubscriptionFrequency()
    {
        $frequency = $this->subscriptionFactory->getFrequency();
        return $this->helper->getSubscriptionFrequency($frequency);
    }

    /**
     * @return float|null
     */
    public function getSubscriptionHowMany()
    {
        $howMany = $this->subscriptionFactory->getHowMany();
        if (isset($howMany) && $howMany != 0) {
            return $howMany;
        } else {
            return null;
        }
    }

    /**
     * @return float
     */
    public function getSubscriptionDiscount()
    {
        return $this->subscriptionFactory->getDiscount();
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->returnSubscriptionId($this->productFactory);
    }

    /**
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
     * @param $product_id
     * @return boolean
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
     * @param $productCollector
     * @return mixed
     */
    private function returnSubscriptionId($productCollector)
    {
        foreach ($productCollector as $item) {
            return $item->getData('subscription_id');
        }
    }

    /**
     * @return mixed
     */
    public function isEnableHowMany()
    {
        return $this->helper->isSubscriptionEnableHowMany();
    }

    /**
     * @return string
     */
    public function getHowManyUnit()
    {
        $frequency = $this->subscriptionFactory->getFrequency();
        $howManyUnit = $this->helper->getHowManyUnits($frequency);
        return $howManyUnit;
    }

    /**
     * @return array|string
     */
    public function getProductType()
    {
        return $this->_coreRegistry->registry('product')->getTypeId();
    }
}
