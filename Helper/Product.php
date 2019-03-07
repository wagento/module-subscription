<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class Product
 * @package Wagento\Subscription\Helper
 */
class Product extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $encoder;

    /**
     * Product constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductRepository $product
     * @param ProductFactory $productFactory
     * @param SubscriptionFactory $subscriptionFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param PriceHelper $priceHelper
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductRepository $product,
        ProductFactory $productFactory,
        SubscriptionFactory $subscriptionFactory,
        \Magento\Checkout\Model\Cart $cart,
        PriceHelper $priceHelper,
        \Magento\Framework\Json\EncoderInterface $encoder
    ) {
    
        parent::__construct($context);
        $this->product = $product;
        $this->productFactory = $productFactory->create();
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->cart = $cart;
        $this->scopeConfig = $context->getScopeConfig();
        $this->priceHelper = $priceHelper;
        $this->encoder = $encoder;
    }

    /**
     * @param $productId
     * @param $qty
     * @return bool
     */
    public function addToCartSubscriptionProduct($productId, $qty, $links, $refreshFlag = null)
    {
        try {
            $params = [
                'product' => $productId,
                'qty' => $qty,
                'links' => $links
            ];
            $_product = $this->product->getById($productId);
            if ($_product) {
                $this->cart->addProduct($_product, $params);
                if ($refreshFlag == null) {
                    $this->cart->save();
                }
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        return true;
    }

    /**
     * @param $productId
     * @return float|null
     */
    public function getInitialFee($productId)
    {
        $productCollection = $this->productFactory->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $productId]);
        $subscriptionData = $this->subscriptionFactory
            ->load($this->returnSubscriptionId($productCollection));
        return $subFee = $subscriptionData->getFee();
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
     * @param $productId
     * @return bool|string
     */
    public function removeToCartSubscriptionProduct($productId)
    {
        try {
            $this->cart->removeItem($productId);
            $this->cart->save();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        return true;
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->product->getById($productId);
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLinksPurchasedSeparately($productId)
    {
        if ($this->getProduct($productId)->getTypeId() == 'downloadable') {
            return ($this->getProduct($productId)->getLinksPurchasedSeparately());
        } else {
            return false;
        }
    }

    /**
     * @param $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasLinks($productId)
    {
        if ($this->getProduct($productId)->getTypeId() == 'downloadable') {
            return $this->getProduct($productId)->getTypeInstance()
                ->hasLinks($this->getProduct($productId));
        } else {
            return false;
        }
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * Return title of links section
     */
    public function getLinksTitle($productId)
    {

        if ($this->getProduct($productId)->getLinksTitle()) {
            return $this->getProduct($productId)->getLinksTitle();
        }
        return $this->scopeConfig->getValue(
            \Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLinkSelectionRequired($productId)
    {
        if ($this->getProduct($productId)->getTypeId() == 'downloadable') {
            return $this->getProduct($productId)->getTypeInstance()
                ->getLinkSelectionRequired($this->getProduct($productId));
        }
    }

    /**
     * @param $link
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLinkAmount($link, $productId)
    {
        return $this->getPriceType($productId)->getLinkAmount($link);
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * Get LinkPrice Type
     */
    protected function getPriceType($productId)
    {
        return $this->getProduct($productId)->getPriceInfo()->getPrice(LinkPrice::PRICE_CODE);
    }

    /**
     * @param Link $link
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLinkPrice(Link $link, $productId)
    {
        return $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $this->getLinkAmount($link, $productId),
            $this->getPriceType($productId),
            $this->getProduct($productId)
        );
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLinks($productId)
    {
        if ($this->getProduct($productId)->getTypeId() == 'downloadable') {
            $links = $this->getProduct($productId)->getTypeInstance()
                ->getLinks($this->getProduct($productId));
            $linksarray = [];
            foreach ($links as $key => $link) {
                $linksarray[$key]['link_id'] = $link->getLinkId();
                $linksarray[$key]['price'] = $this->priceHelper->currency(number_format($link->getPrice(), 2), true, false);
                $linksarray[$key]['title'] = $link->getTitle();
            }
            return $linksarray;
        }
    }

    /**
     * @param $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJsonConfig1($productId)
    {
        $finalPrice = $this->getProduct($productId)->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE);

        $linksConfig = [];
        foreach ($this->getLinks($productId) as $link) {
            $amount = $finalPrice->getCustomAmount($link->getPrice());
            $linksConfig[$link->getId()] = [
                'finalPrice' => $amount->getValue(),
                'basePrice' => $amount->getBaseAmount()
            ];
        }

        return $this->encoder->encode(['links' => $linksConfig]);
    }

    /**
     * @param $link
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLinkSampleUrl($link, $productId)
    {
        $store = $this->getProduct($productId)->getStore();
        return $store->getUrl('downloadable/download/linkSample', ['link_id' => $link->getId()]);
    }

    /**
     * @param $link
     * @param $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * Returns value for link's input checkbox - either 'checked' or '
     */
    public function getLinkCheckedValue($link, $productId)
    {
        return $this->getIsLinkChecked($link, $productId) ? 'checked' : '';
    }

    /**
     * @param $link
     * @param $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * Returns whether link checked by default or not
     */
    public function getIsLinkChecked($link, $productId)
    {
        $configValue = $this->getProduct($productId)->getPreconfiguredValues()->getLinks();
        if (!$configValue || !is_array($configValue)) {
            return false;
        }

        return $configValue && in_array($link->getId(), $configValue);
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsOpenInNewWindow()
    {
        return $this->scopeConfig->isSetFlag(
            \Magento\Downloadable\Model\Link::XML_PATH_TARGET_NEW_WINDOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
