<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\CustomerData;

use Magento\Wishlist\CustomerData\Wishlist as CoreWishlist;

/**
 * Wishlist section
 */
class Wishlist extends CoreWishlist
{

    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view
    ) {
    
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view);
    }

    /**
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * Retrieve wishlist item data
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        $dataArray = [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true)
        ];

        $subscriptionAttribute = $product->getCustomAttribute('subscription_configurate');
        if (isset($subscriptionAttribute)) {
            $subAttributeValue = ['subscription_configurate' => $subscriptionAttribute->getValue()];
            array_push($dataArray, $subAttributeValue);
        }
        return $dataArray;
    }
}
