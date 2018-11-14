<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Helper\Data;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionFactory;
use Wagento\Subscription\Helper\Data as SubscriptionHelper;

class SubAdditionalOption implements ObserverInterface
{
    const FLOAT_VALUE = 0.0000;
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var mixed
     */
    private $_serializer;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var SubscriptionHelper
     */
    protected $subHelper;

    /**
     * SubAdditionalOption constructor.
     * @param RequestInterface $request
     * @param Json $serializer
     * @param SubscriptionFactory $subscriptionFactory
     * @param ProductFactory $productFactory
     * @param Data $priceHelper
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param SubscriptionHelper $subHelper
     */
    public function __construct(
        RequestInterface $request,
        Json $serializer,
        SubscriptionFactory $subscriptionFactory,
        ProductFactory $productFactory,
        Data $priceHelper,
        \Magento\Framework\Json\Helper\Data $helper,
        SubscriptionHelper $subHelper
    ) {
    
        $this->_request = $request;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->productFactory = $productFactory->create();
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->priceHelper = $priceHelper;
        $this->helper = $helper;
        $this->subHelper = $subHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $this->_request->getFullActionName();
        if ($action == 'subscription_ajax_subscribe' || $action == 'subscription_ajax_subscribecart') {
            $product = $observer->getProduct();
            $subOptionsJson = $this->helper->jsonDecode($this->_request->getContent());
            $subOptions = $this->helper->jsonDecode($subOptionsJson);

            if ($subOptions['isEnableHowMany'] == 1) {
                $howMany = $subOptions['subHowMany'];
            }
            if ($subOptions['isEnableHowMany'] == 0) {
                $howMany = $subOptions['howMany'];
            }

            $productCollection = $this->productFactory->getCollection()
                ->addFieldToFilter('product_id', ['eq' => $product->getId()]);
            $subscriptionData = $this->subscriptionFactory
                ->load($this->returnSubscriptionId($productCollection));
            $subscriptionFrequency = $subscriptionData->getFrequency();
            $subDiscount = $subscriptionData->getDiscount();
            $howManyUnits = $this->subHelper->getHowManyUnits($subscriptionFrequency);
            $subFee = $subscriptionData->getFee();

            $additionalOptions = [];
            $additionalOptions[] = [
                [
                    'label' => __("Subscription Plan Name"),
                    'value' => $subscriptionData->getName()
                ],
                [
                    'label' => __("Frequency"),
                    'value' => $this->subHelper->getSubscriptionFrequency($subscriptionFrequency)
                ],
            ];

            if ($howMany != 0) {
                $howManyOption = ['label' => __("Subscription Cycle"), 'value' => $howMany . " " . $howManyUnits];
                array_push($additionalOptions[0], $howManyOption);
            }
            if ($subDiscount != self::FLOAT_VALUE) {
                $subDiscountWithCurrency = $this->priceHelper
                    ->currency(
                        number_format($subDiscount, 2),
                        true,
                        false
                    );
                $discountOption = ['label' => __("Discount"), 'value' => $subDiscountWithCurrency];
                array_push($additionalOptions[0], $discountOption);
            }

            if ($subFee != self::FLOAT_VALUE) {
                $subFeeWithCurrency = $this->priceHelper
                    ->currency(
                        number_format($subFee, 2),
                        true,
                        false
                    );
                $initialFeeOption = ['label' => __("Initial Fee"), 'value' => $subFeeWithCurrency];
                array_push($additionalOptions[0], $initialFeeOption);
            }

            foreach ($additionalOptions as $key => $subOption) {
                $product->addCustomOption(
                    'additional_options',
                    $this->_serializer->serialize($subOption)
                );
            }
        }
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
}
