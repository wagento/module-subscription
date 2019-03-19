<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Wagento\Subscription\Helper\Data as SubscriptionHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\AddressFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class SubscriptionService
 * @package Wagento\Subscription\Model
 */
class SubscriptionService
{
    const FLOAT_VALUE = 0.0000;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulator;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productModel;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $_quoteManagementModel;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @var
     */
    protected $subProductFactory;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var TimezoneInterface
     */
    protected $dateProcessor;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $tokenRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var GetPaymentNonceCommand
     */
    protected $getPaymentNonceCommand;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteItemFactory
     */
    protected $quoteItem;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var AddressFactory
     */
    protected $address;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var
     */
    private $productRepository;


    /**
     * SubscriptionService constructor.
     * @param \Magento\Store\Model\App\Emulation $emulator
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagementModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param Json $serializer
     * @param \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory
     * @param \Wagento\Subscription\Model\ProductFactory $subProductFactory
     * @param SubscriptionHelper $subHelper
     * @param PriceHelper $priceHelper
     * @param TimezoneInterface $dateProcessor
     * @param PaymentHelper $paymentHelper
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\ProductMetadata|null $productMetadata
     * @param GetPaymentNonceCommand $getPaymentNonceCommand
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSession
     * @param QuoteItemFactory $quoteItem
     * @param AddressConfig $addressConfig
     * @param AddressFactory $address
     * @param OrderSender $orderSender
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulator,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Model\QuoteManagement $quoteManagementModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Json $serializer,
        SubscriptionFactory $subscriptionFactory,
        ProductFactory $subProductFactory,
        SubscriptionHelper $subHelper,
        PriceHelper $priceHelper,
        TimezoneInterface $dateProcessor,
        PaymentHelper $paymentHelper,
        PaymentTokenRepositoryInterface $tokenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\ProductMetadata $productMetadata = null,
        GetPaymentNonceCommand $getPaymentNonceCommand,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        QuoteItemFactory $quoteItem,
        AddressConfig $addressConfig,
        AddressFactory $address,
        OrderSender $orderSender,
        ProductRepositoryInterface $productRepository
    ) {
        $this->emulator = $emulator;
        $this->_quoteFactory = $quoteFactory;
        $this->_orderModel = $orderModel;
        $this->_productModel = $productModel;
        $this->_customerRepository = $customerRepository;
        $this->_quoteManagementModel = $quoteManagementModel;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->subscriptionFactory = $subscriptionFactory->create();
        $this->subProductFactory = $subProductFactory->create();
        $this->subHelper = $subHelper;
        $this->priceHelper = $priceHelper;
        $this->dateProcessor = $dateProcessor;
        $this->paymentHelper = $paymentHelper;
        $this->tokenRepository = $tokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getPaymentNonceCommand = $getPaymentNonceCommand;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItem = $quoteItem;
        $this->addressConfig = $addressConfig;
        $this->address = $address;
        $this->orderSender = $orderSender;
        $this->productRepository = $productRepository;
        if ($productMetadata === null) {
            // Optional class dependency to preserve backwards compatibility on @api class.
            $this->productMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\ProductMetadata::class
            );
        } else {
            $this->productMetadata = $productMetadata;
        }
    }

    /**
     * @param $subscriptions
     * @return mixed
     */
    public function generateOrder($subscriptions)
    {
        $firstSubscription = current($subscriptions);
        $this->emulator->startEnvironmentEmulation($firstSubscription->getStoreId());
        try {
            $response = $this->generateQuote($subscriptions);
            $this->emulator->stopEnvironmentEmulation();
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;
        }
        $this->emulator->stopEnvironmentEmulation();
        return $response;
    }

    /**
     * @param $subscriptions
     * @return mixed
     */
    public function generateQuote($subscriptions)
    {
        $firstSubscription = current($subscriptions);
        $customerId = $firstSubscription->getCustomerId();
        $orderId = $firstSubscription->getSubscribeOrderId();
        $subItemId = $firstSubscription->getSubOrderItemId();
        $storeId = $firstSubscription->getStoreId();

        $_order = $this->_orderModel->create()->load($orderId);
        $subProductId = $this->getSubscriptionProductId($_order, $subItemId);

        $price = $this->getSubscriptionProductPrice($_order, $subItemId);
        $subQty = $this->getSubscriptionProductQty($_order, $subItemId);

        $publicHash = $firstSubscription->getPublicHash();

        $subBillAddressId = $firstSubscription->getBillingAddressId();
        $billAddress = $this->getBillingAddress($_order, $subBillAddressId);

        $paymentMethod = $this->getPaymentMethod($_order);
        if (!$paymentMethod || $paymentMethod == '' || $paymentMethod == "NULL") {
            $response['error_msg'] = __('Payment Method not found');
            $response['error'] = true;
            return $response;
        }

        try {
            $customer = $this->_customerRepository->getById($customerId);
            $quote = $this->_quoteFactory->create();

            $subName = $firstSubscription->getSubName();
            $subscriptionFrequency = $firstSubscription->getSubFrequency();
            $howMany = $firstSubscription->getHowMany();
            $subDiscount = $firstSubscription->getSubDiscount();
            $subFee = $firstSubscription->getSubFee();
            $howManyUnits = $this->subHelper->getHowManyUnits($subscriptionFrequency);
            $additionalOptions = $this->getSubscriptionOptions($subName, $subscriptionFrequency, $howMany, $subDiscount, $subFee, $howManyUnits);

            $product = $this->_productModel->create()->load($subProductId);
            if (!empty($additionalOptions)) {
                foreach ($additionalOptions as $key => $subOption) {
                    $product->addCustomOption(
                        'additional_options',
                        $this->_serializer->serialize($subOption)
                    );
                }
            }

            $quote->setStoreId($storeId);

            /*customer details*/
            $quote->assignCustomer($customer);
            $quote->setCustomerEmail($_order->getCustomerEmail());

            /*Quote Item details*/
            $quoteItem = $quote->addProduct($product);
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setQty($subQty);

            if (isset($publicHash)) {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('public_hash', $publicHash)
                    ->setPageSize(1)
                    ->create();
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();

                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            } else {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('entity_id', $firstSubscription->getPaymentTokenId())
                    ->setPageSize(1)
                    ->create();
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();
                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            }

            $payment = $quote->getPayment();
            if (isset($card)) {
                $result = $this->getPaymentNonceCommand->execute(['public_hash' => $card->getPublicHash(), 'customer_id' => $card->getCustomerId()])->get();
                if (version_compare($this->productMetadata->getVersion(), '2.1.3', '>=')) {
                    $payment->setAdditionalInformation('customer_id', $card->getCustomerId());
                    $payment->setAdditionalInformation('public_hash', $card->getPublicHash());
                    $payment->setAdditionalInformation('payment_method_nonce', $result['paymentMethodNonce']);
                } else {
                    $payment->setAdditionalInformation(
                        'token_metadata',
                        [
                            'customer_id' => $card->getCustomerId(),
                            'public_hash' => $card->getPublicHash(),
                            'payment_method_nonce' => $result['paymentMethodNonce'],
                        ]
                    );
                }
            }

            $payment->setMethod($paymentMethod);
            $quote->getPayment()->setQuote($quote);
            $quote->getBillingAddress()->addData($billAddress);

            /*Add shiiping if its not virtual order */
            $isShippingRequired = $this->getIsShippingRequired($subProductId);
            if($isShippingRequired) {
                $shippingMethod = $this->getShippingMethod($_order);
                $subShipAddressId = $firstSubscription->getShippingAddressId();
                $shipAddress = $this->getShippingAddress($_order, $subShipAddressId);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                    ->setShippingMethod($shippingMethod);

                $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            } else {
                $quote->isVirtual(true);
            }
            $quote->setPaymentMethod($paymentMethod);
            $quote->collectTotals();

            // This second collectTotals pulls in the new shipping amount.
            $quote->setTotalsCollectedFlag(false)->collectTotals();

            $quote->setInventoryProcessed(false);
            $quote->save();

            $order = $this->_quoteManagementModel->submit($quote);
            $order_id = $order->getId();
            $this->orderSender->send($order, true);

            if (isset($order_id) && !empty($order_id)) {
                $order = $this->orderRepository->get($order_id);
                $this->deleteQuoteItems(); //Delete cart items
                $response['success'] = true;
                $response['success_data']['increment_id'] = $order->getIncrementId();
                $response['success_data']['next_renewed'] = $this->getNextRenewDate($subscriptionFrequency);
            }
        } catch (\Exception $ex) {
            $response['error_msg'] = $ex->getMessage();
            $response['error'] = true;
        }
        return $response;
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductId($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getProductId();
            }
        }
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductPrice($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getPrice();
            }
        }
    }

    /**
     * @param $shippingAddressId
     * @return array
     */
    public function getSubAddress($shippingAddressId)
    {
        $address = $this->address->create()->load($shippingAddressId);
        $shipAddressData = [
            "firstname" => $address->getFirstname(),
            "lastname" => $address->getLastname(),
            "street" => $address->getStreet(),
            "city" => $address->getCity(),
            "postcode" => $address->getPostcode(),
            "telephone" => $address->getTelephone(),
            "country_id" => $address->getCountryId(),
            "region_id" => $address->getRegionId(),
        ];
        return $shipAddressData;
    }

    /**
     * @param $_order
     * @param $shippingAddressId
     * @return array
     */
    public function getShippingAddress($_order, $shippingAddressId)
    {
        if (isset($shippingAddressId)) {
            $shipAddressData = $this->getSubAddress($shippingAddressId);
            return $shipAddressData;
        } else {
            $shippingAddress = $_order->getShippingAddress();
            if($shippingAddress!=NULL) {
                $shipAddressData = [
                    "firstname" => $shippingAddress->getFirstname(),
                    "lastname" => $shippingAddress->getLastname(),
                    "street" => $shippingAddress->getStreet(),
                    "city" => $shippingAddress->getCity(),
                    "postcode" => $shippingAddress->getPostcode(),
                    "telephone" => $shippingAddress->getTelephone(),
                    "country_id" => $shippingAddress->getCountryId(),
                    "region_id" => $shippingAddress->getRegionId(),
                ];
                return $shipAddressData;
            }
        }
        return NULL;
    }

    /**
     * @param $_order
     * @param $billingAddressId
     * @return array
     */
    public function getBillingAddress($_order, $billingAddressId)
    {
        if (isset($billingAddressId)) {
            $billAddressData = $this->getSubAddress($billingAddressId);
        } else {
            $billingAddress = $_order->getBillingAddress();
            $billAddressData = [
                "firstname" => $billingAddress->getFirstname(),
                "lastname" => $billingAddress->getLastname(),
                "street" => $billingAddress->getStreet(),
                "city" => $billingAddress->getCity(),
                "postcode" => $billingAddress->getPostcode(),
                "telephone" => $billingAddress->getTelephone(),
                "country_id" => $billingAddress->getCountryId(),
                "region_id" => $billingAddress->getRegionId(),
            ];
        }
        return $billAddressData;
    }

    /**
     * @param $_order
     * @return mixed
     */
    public function getShippingMethod($_order)
    {
        return $_order->getShippingMethod();
    }

    /**
     * @param $_order
     * @return mixed
     */
    public function getPaymentMethod($_order)
    {
        return $_order->getPayment()->getMethod();
    }

    /**
     * @param $_order
     * @param $itemId
     * @return mixed
     */
    public function getSubscriptionProductQty($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                return $item->getQtyOrdered();
            }
        }
    }

    /**
     * @param $subName
     * @param $subscriptionFrequency
     * @param $howMany
     * @param $subDiscount
     * @param $subFee
     * @param $howManyUnits
     * @return array
     */
    public function getSubscriptionOptions($subName, $subscriptionFrequency, $howMany, $subDiscount, $subFee, $howManyUnits)
    {
        $additionalOptions = [];
        $additionalOptions[] = [
            [
                'label' => __("Subscription Plan Name"),
                'value' => $subName
            ],
            [
                'label' => __("Frequency"),
                'value' => $this->subHelper->getSubscriptionFrequency($subscriptionFrequency)
            ],
            [
                'label' => __("Subscription Cycle"),
                'value' => $howMany . " " . $howManyUnits
            ],
        ];
        if ($subDiscount != self::FLOAT_VALUE) {
            $subDiscountWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subDiscount, 2),
                    true,
                    false
                );
            $discountOption = ['label' => "Discount", 'value' => $subDiscountWithCurrency];
            array_push($additionalOptions[0], $discountOption);
        }

        if ($subFee != self::FLOAT_VALUE) {
            $subFeeWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subFee, 2),
                    true,
                    false
                );
            $initialFeeOption = ['label' => "Initial Fee", 'value' => $subFeeWithCurrency];
            array_push($additionalOptions[0], $initialFeeOption);
        }
        return $additionalOptions;
    }

    /**
     * @param $frequency
     * @return string
     */
    public function calculateNextRun($frequency)
    {
        $now = $this->dateProcessor->date(null, null, false);
        $date = $now->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);

        /*Daily */
        if ($frequency == 1) {
            $newDate = strtotime('+1 Day', strtotime($date));
            $daily = $this->dateProcessor->date($newDate);
            $nextRunDate = $daily->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }
        //Weekly
        if ($frequency == 2) {
            $newDate = strtotime('+1 Week', strtotime($date));
            $weekly = $this->dateProcessor->date($newDate);
            $nextRunDate = $weekly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }

        //Monthly
        if ($frequency == 3) {
            $newDate = strtotime('+1 Month', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }

        //Yearly
        if ($frequency == 4) {
            $newDate = strtotime('+1 Year', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        }
        if (isset($nextRunDate)) {
            return $nextRunDate;
        } else {
            return false;
        }
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function isQuoteTokenBase(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        if (in_array($quote->getPayment()->getMethod(), $this->getAllMethods())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAllMethods()
    {
        $methods = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (isset($data['group']) && $data['group'] == 'tokenbase') {
                $methods[] = $code;
            }
        }

        return $methods;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteQuoteItems()
    {
        $checkoutSession = $this->checkoutSession;
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all teh items in session
        foreach ($allItems as $item) {
            $itemId = $item->getItemId();//item id of particular item
            $quoteItem = $this->quoteItem->create()->load($itemId);//load particular item which you want to delete by his item id
            $quoteItem->delete();//deletes the item
        }
        return true;
    }

    /**
     * @param $subscriptionFrequency
     * @return string
     */
    public function getNextRenewDate($subscriptionFrequency)
    {
        return $this->calculateNextRun($subscriptionFrequency);
    }

    public function getIsShippingRequired($productId) {
        $product = $this->productRepository->getById($productId);
        $productTypes = ['virtual', 'downloadable'];

        if ($product) {
            $productType = $product->getTypeId();
            if (!in_array($productType, $productTypes)) {
                return true;
            }
        }
        return false;
    }
}