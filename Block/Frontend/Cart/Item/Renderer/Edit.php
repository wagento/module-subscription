<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Frontend\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;

class Edit extends Generic
{
    /**
     * @var \Wagento\Subscription\Model\Product
     */
    public $productFactory;

    /**
     * @var \Wagento\Subscription\Model\Subscription
     */
    public $subscriptionFactory;
    /**
     * @var \Wagento\Subscription\Helper\Subscription
     */
    public $subscription;

    /**
     * @var \Wagento\Subscription\Helper\Data
     */
    public $subscriptionDataHelper;

    /**
     * Edit constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Wagento\Subscription\Model\ProductFactory $productFactory
     * @param \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory
     * @param \Wagento\Subscription\Helper\Subscription $subscription
     * @param \Wagento\Subscription\Helper\Data $subscriptionDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Wagento\Subscription\Model\ProductFactory $productFactory,
        \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory,
        \Wagento\Subscription\Helper\Subscription $subscription,
        \Wagento\Subscription\Helper\Data $subscriptionDataHelper,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->productFactory = $productFactory->create();
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->subscription = $subscription;
        $this->subHelperData = $subscriptionDataHelper;
    }

    /**
     * Get subscription function.
     *
     * @return null|string
     */
    public function getSubscription()
    {
        $data = $this->getSubscriptionData();
        return $data;
    }

    /**
     * Get subscription frequency function.
     *
     * @return string
     */
    public function getSubscriptionFrequency()
    {
        $frequency = $this->subscriptionFactory->getFrequency();
        return $this->subHelperData->getSubscriptionFrequency($frequency);
    }

    /**
     * Get subscription data function.
     *
     * @return mixed
     */
    public function getSubscriptionData()
    {
        return $this->subscription->getSubscriptionData($this->getItem()->getProduct()->getId());
    }

    /**
     * Get product id function.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getItem()->getProduct()->getId();
    }
}
