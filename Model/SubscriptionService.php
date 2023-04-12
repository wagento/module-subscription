<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Quote\Model\Quote\ItemFactory as QuoteItemFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use PayPal\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Wagento\Subscription\Helper\Data as SubscriptionHelper;

class SubscriptionService
{
    public const FLOAT_VALUE = 0.0000;

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
     * @var Product
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /** @var SubscriptionHelper  */
    private $subHelper;

    /**
     * SubscriptionService constructor.
     *
     * @param \Magento\Store\Model\App\Emulation $emulator
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagementModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param Json $serializer
     * @param ProductFactory $subProductFactory
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
     * @param ProductRepositoryInterface $productRepository
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
        if (null === $productMetadata) {
            // Optional class dependency to preserve backwards compatibility on @api class.
            $this->productMetadata = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\ProductMetadata::class
            );
        } else {
            $this->productMetadata = $productMetadata;
        }
    }

    /**
     * Generate order function.
     *
     * @param array|mixed $subscriptions
     * @return array|mixed
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
     * Generate quote function.
     *
     * @param array|mixed $subscriptions
     *
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
        if (!$paymentMethod || '' == $paymentMethod || 'NULL' == $paymentMethod) {
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
            $additionalOptions = $this->getSubscriptionOptions(
                $subName,
                $subscriptionFrequency,
                $howMany,
                $subDiscount,
                $subFee,
                $howManyUnits
            );

            $product = $this->_productModel->create()->load($subProductId);
            if (!empty($additionalOptions)) {
                foreach ($additionalOptions as $key => $subOption) {
                    $product->addCustomOption(
                        'additional_options',
                        $this->_serializer->serialize($subOption)
                    );
                }
            }

            $request = new \Magento\Framework\DataObject();
            $links = $this->getSubscriptionProductLinks($_order, $subItemId);
            if ($links) {
                $request->setData('links', $links);
            }
            $quote->setStoreId($storeId);

            // customer details
            $quote->assignCustomer($customer);
            $quote->setCustomerEmail($_order->getCustomerEmail());

            // Quote Item details
            $quoteItem = $quote->addProduct($product, $request);
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setQty($subQty);

            if (isset($publicHash)) {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('public_hash', $publicHash)
                    ->setPageSize(1)
                    ->create()
                ;
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();

                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            } else {
                $tokenCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId)
                    ->addFilter('entity_id', $firstSubscription->getPaymentTokenId())
                    ->setPageSize(1)
                    ->create()
                ;
                $tokens = $this->tokenRepository->getList($tokenCriteria)->getItems();
                if (!empty($tokens)) {
                    $card = array_shift($tokens);
                }
            }

            $payment = $quote->getPayment();
            if (isset($card)) {
                $result = $this->getPaymentNonceCommand
                    ->execute(
                        ['public_hash' => $card->getPublicHash(), 'customer_id' => $card->getCustomerId()]
                    )->get();
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

            // Add shiiping if its not virtual order
            $isShippingRequired = $this->getIsShippingRequired($subProductId);
            if ($isShippingRequired) {
                $shippingMethod = $this->getShippingMethod($_order);
                $subShipAddressId = $firstSubscription->getShippingAddressId();
                $shipAddress = $this->getShippingAddress($_order, $subShipAddressId);
                $shippingAddress = $quote->getShippingAddress()->addData($shipAddress);
                $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                    ->setShippingMethod($shippingMethod)
                ;

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
                $this->deleteQuoteItems(); // Delete cart items
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
     * Get subscription product id function.
     *
     * @param mixed $_order
     * @param mixed $itemId
     * @return mixed|void
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
     *  Get subscription product price function.
     *
     * @param mixed $_order
     * @param mixed $itemId
     *
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
     * Get sub address function.
     *
     * @param mixed $shippingAddressId
     *
     * @return array
     */
    public function getSubAddress($shippingAddressId)
    {
        $address = $this->address->create()->load($shippingAddressId);

        return [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'country_id' => $address->getCountryId(),
            'region_id' => $address->getRegionId(),
        ];
    }

    /**
     * Get shipping address id.
     *
     * @param mixed $_order
     * @param mixed $shippingAddressId
     *
     * @return array
     */
    public function getShippingAddress($_order, $shippingAddressId)
    {
        if (isset($shippingAddressId)) {
            return $this->getSubAddress($shippingAddressId);
        }
        $shippingAddress = $_order->getShippingAddress();
        if (null != $shippingAddress) {
            return [
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'street' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'postcode' => $shippingAddress->getPostcode(),
                'telephone' => $shippingAddress->getTelephone(),
                'country_id' => $shippingAddress->getCountryId(),
                'region_id' => $shippingAddress->getRegionId(),
            ];
        }

        return null;
    }

    /**
     * Get billing address id function.
     *
     * @param mixed $_order
     * @param mixed $billingAddressId
     *
     * @return array
     */
    public function getBillingAddress($_order, $billingAddressId)
    {
        if (isset($billingAddressId)) {
            $billAddressData = $this->getSubAddress($billingAddressId);
        } else {
            $billingAddress = $_order->getBillingAddress();
            $billAddressData = [
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => $billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'country_id' => $billingAddress->getCountryId(),
                'region_id' => $billingAddress->getRegionId(),
            ];
        }

        return $billAddressData;
    }

    /**
     * Get shipping method function.
     *
     * @param mixed $_order
     *
     * @return mixed
     */
    public function getShippingMethod($_order)
    {
        return $_order->getShippingMethod();
    }

    /**
     * Get payment method function.
     *
     * @param mixed $_order
     *
     * @return mixed
     */
    public function getPaymentMethod($_order)
    {
        return $_order->getPayment()->getMethod();
    }

    /**
     * Get subscription product qty function.
     *
     * @param mixed $_order
     * @param mixed $itemId
     *
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
     * Get subscription options function.
     *
     * @param string $subName
     * @param string $subscriptionFrequency
     * @param string $howMany
     * @param mixed $subDiscount
     * @param string $subFee
     * @param string $howManyUnits
     *
     * @return array
     */
    public function getSubscriptionOptions(
        $subName,
        $subscriptionFrequency,
        $howMany,
        $subDiscount,
        $subFee,
        $howManyUnits
    ) {
        $additionalOptions = [];
        $additionalOptions[] = [
            [
                'label' => __('Subscription Plan Name'),
                'value' => $subName,
            ],
            [
                'label' => __('Frequency'),
                'value' => $this->subHelper->getSubscriptionFrequency($subscriptionFrequency),
            ],
            [
                'label' => __('Subscription Cycle'),
                'value' => $howMany.' '.$howManyUnits,
            ],
        ];
        if (self::FLOAT_VALUE != $subDiscount) {
            $subDiscountWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subDiscount, 2),
                    true,
                    false
                )
            ;
            $discountOption = ['label' => 'Discount', 'value' => $subDiscountWithCurrency];
            array_push($additionalOptions[0], $discountOption);
        }

        if (self::FLOAT_VALUE != $subFee) {
            $subFeeWithCurrency = $this->priceHelper
                ->currency(
                    number_format($subFee, 2),
                    true,
                    false
                )
            ;
            $initialFeeOption = ['label' => 'Initial Fee', 'value' => $subFeeWithCurrency];
            array_push($additionalOptions[0], $initialFeeOption);
        }

        return $additionalOptions;
    }

    /**
     * Calculate next run price.
     *
     * @param string $frequency
     *
     * @return string
     */
    public function calculateNextRun($frequency)
    {
        $now = $this->dateProcessor->date(null, null, false);
        $date = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        // Daily
        if (1 == $frequency) {
            $newDate = strtotime('+1 Day', strtotime($date));
            $daily = $this->dateProcessor->date($newDate);
            $nextRunDate = $daily->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        // Weekly
        if (2 == $frequency) {
            $newDate = strtotime('+1 Week', strtotime($date));
            $weekly = $this->dateProcessor->date($newDate);
            $nextRunDate = $weekly->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }

        // Monthly
        if (3 == $frequency) {
            $newDate = strtotime('+1 Month', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }

        // Yearly
        if (4 == $frequency) {
            $newDate = strtotime('+1 Year', strtotime($date));
            $monthly = $this->dateProcessor->date($newDate);
            $nextRunDate = $monthly->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        if (isset($nextRunDate)) {
            return $nextRunDate;
        }

        return false;
    }

    /**
     * Quote token base function.
     *
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
     * Get all methods function.
     *
     * @return array
     */
    public function getAllMethods()
    {
        $methods = [];

        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if (isset($data['group']) && 'tokenbase' == $data['group']) {
                $methods[] = $code;
            }
        }

        return $methods;
    }

    /**
     * Delete quote items function.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteQuoteItems()
    {
        $checkoutSession = $this->checkoutSession;
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems(); // returns all teh items in session
        foreach ($allItems as $item) {
            $itemId = $item->getItemId(); // item id of particular item
            $quoteItem = $this->quoteItem->create()->load($itemId);
            // load particular item which you want to delete by his item id
            $quoteItem->delete(); // deletes the item
        }

        return true;
    }

    /**
     * Get next renewdate function.
     *
     * @param mixed $subscriptionFrequency
     * @return false|string
     */
    public function getNextRenewDate($subscriptionFrequency)
    {
        return $this->calculateNextRun($subscriptionFrequency);
    }

    /**
     * Get shipping required function.
     *
     * @param mixed $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsShippingRequired($productId)
    {
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

    /**
     * Get subscription product links function.
     *
     * @param mixed $_order
     * @param mixed $itemId
     * @return false|mixed
     */
    public function getSubscriptionProductLinks($_order, $itemId)
    {
        $items = $_order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getItemId() == $itemId) {
                $itemOptons = $item->getProductOptionByCode('info_buyRequest');
                if (isset($itemOptons['links'])) {
                    return $itemOptons['links'];
                }
            }
        }

        return false;
    }
}
