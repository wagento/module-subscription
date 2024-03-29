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

class Subscription extends AbstractHelper
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * Subscription constructor.
     *
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param SubscriptionFactory $subscriptionFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        SubscriptionFactory $subscriptionFactory
    ) {
        parent::__construct($context);

        $this->productFactory = $productFactory;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * Subscription data function.
     *
     * @param mixed $productId
     *
     * @return mixed
     */
    public function getSubscriptionData($productId)
    {
        $this->productFactory = $this->initProduct($productId);
        $this->subscriptionFactory = $this->initSubcription($this->productFactory);

        return $this->subscriptionFactory->getData();
    }

    /**
     * Init product function.
     *
     * @param mixed $productId
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    private function initProduct($productId)
    {
        return $this->productFactory->create()->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $productId])
        ;
    }

    /**
     * Init subcription function.
     *
     * @param mixed $productFactory
     *
     * @return \Wagento\Subscription\Model\Subscription
     */
    private function initSubcription($productFactory)
    {
        return $this->subscriptionFactory->create()
            ->load((int) $this->returnSubscriptionId($productFactory))
        ;
    }

    /**
     * Return subscription id function.
     *
     * @param mixed $productFactory
     *
     * @return mixed
     */
    private function returnSubscriptionId($productFactory)
    {
        foreach ($productFactory as $item) {
            return $item->getData('subscription_id');
        }
    }
}
