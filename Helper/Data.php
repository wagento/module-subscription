<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Helper;

use Magento\Catalog\Model\Product\Type\Price;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Magento\Sales\Model\Order\AddressRepository;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionRepository;

class Data extends AbstractHelper
{
    public const XML_PATH_ENABLE_SUB = 'braintree_subscription/subscription_config/enable';
    public const XML_PATH_SUBSCRIPTION_LABEL = 'braintree_subscription/subscription_config/product_option_label';
    public const XML_PATH_SUBSCRIPTION_CANCEL = 'braintree_subscription/subscription_config
    /customer_cancel_subscription';
    public const XML_PATH_SUBSCRIPTION_PAUSE = 'braintree_subscription/subscription_config/customer_pause_subscription';
    public const XML_PATH_ENABLE_HOWMANY = 'braintree_subscription/subscription_config/customer_howmany_subscription';

    public const FLOAT_VALUE = 0.0000;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @var \Wagento\Subscription\Model\ProductFactory
     */
    protected $subProductFactory;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var AddressFactory
     */
    protected $address;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var AddressConfig
     */
    protected $addressConfig;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @var CcConfig
     */
    protected $ccCongig;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * @var Price
     */
    protected $price;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $links;

    /**
     * @var \Magento\Customer\Model\Session|Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Json $serializer
     * @param ProductFactory $subProductFactory
     * @param SubscriptionRepository $subscriptionRepository
     * @param ProductRepository $productRepository
     * @param CheckoutSession $checkoutSession
     * @param AddressFactory $address
     * @param EventManager $eventManager
     * @param AddressConfig $addressConfig
     * @param CustomerFactory $customer
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param AddressRepository $addressRepository
     * @param Price $price
     * @param \Magento\Downloadable\Model\Link $links
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        Json $serializer,
        ProductFactory $subProductFactory,
        SubscriptionRepository $subscriptionRepository,
        ProductRepository $productRepository,
        CheckoutSession $checkoutSession,
        AddressFactory $address,
        EventManager $eventManager,
        AddressConfig $addressConfig,
        CustomerFactory $customer,
        PaymentTokenManagementInterface $paymentTokenManagement,
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        AddressRepository $addressRepository,
        Price $price,
        \Magento\Downloadable\Model\Link $links,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->subProductFactory = $subProductFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->address = $address;
        $this->eventManager = $eventManager;
        $this->addressConfig = $addressConfig;
        $this->customer = $customer;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->ccCongig = $ccConfig;
        $this->paymentHelper = $paymentHelper;
        $this->addressRepository = $addressRepository;
        $this->price = $price;
        $this->links = $links;
        $this->customerSession = $customerSession;
    }

    /**
     * Subscription module enable function.
     *
     * @return mixed
     */
    public function isSubscriptionModuleEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_SUB, $storeScope);
    }

    /**
     * Subscription frequency function.
     *
     * @param mixed $subFrequency
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionFrequency($subFrequency)
    {
        $frequencyLabels = [
            1 => 'Daily',
            2 => 'Weekly',
            3 => 'Monthly',
            4 => 'Yearly',
        ];

        if (array_key_exists($subFrequency, $frequencyLabels)) {
            return __($frequencyLabels[$subFrequency]);
        }

        return __('N/A');
    }

    /**
     * Subscription status function.
     *
     * @param bool $status
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionStatus($status)
    {
        $statusLabels = [
            0 => ('Canceled'),
            1 => ('Active'),
            2 => ('Paused'),
            3 => ('Completed'),
        ];

        if (array_key_exists($status, $statusLabels)) {
            return ($statusLabels[$status]);
        }

        return ('N/A');
    }

    /**
     * Get no of units.
     *
     * @param int $subFrequency
     *
     * @return string
     */
    public function getHowManyUnits($subFrequency)
    {
        if (1 == $subFrequency) {
            return 'Day(s)';
        }
        if (2 == $subFrequency) {
            return 'Week(s)';
        }
        if (3 == $subFrequency) {
            return 'Month(s)';
        }
        if (4 == $subFrequency) {
            return 'Year(s)';
        }
    }

    /**
     * @param $data
     * @return bool|false|string
     */
    public function getJsonEncode($data)
    {
        return $this->_serializer->serialize($data); // it's same as like json_encode
    }

    /**
     * Get no of subscription.
     *
     * @param string $additionalOptions
     *
     * @return false|int|string
     */
    public function getSubscriptionHowMany($additionalOptions)
    {
        if (!empty($additionalOptions)) {
            $options = $this->_serializer->unserialize($additionalOptions);
            $howManyValue = $options['additional_options'][2]['value'];

            return preg_replace('/[^0-9]/', '', $howManyValue);
        }

        return __('N/A');
    }

    /**
     * Get product option label.
     *
     * @return array mixed
     */
    public function getProductOptionLabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_LABEL, $storeScope);
    }

    /**
     * Get subscription id function.
     *
     * @param int $productId
     *
     * @return mixed
     */
    public function getSubscriptionId($productId)
    {
        $subProduct = $this->subProductFactory
            ->create()->getCollection()->addFieldToFilter('product_id', ['eq' => $productId]);

        return $subProduct->getFirstItem()->getSubscriptionId();
    }

    /**
     * Get subscription configurations.
     *
     * @param mixed $productId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return mixed
     */
    public function getSubscriptionConfigurations($productId)
    {
        $productData = $this->productRepository->getById($productId);
        $subConfig = $productData->getCustomAttribute('subscription_configurate');
        if ($subConfig) {
            return $subConfig->getValue();
        }
    }

    /**
     * Get sub name function.
     *
     * @param int $subscriptionId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return null|string
     */
    public function getSubName($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        return $subscription->getName();
    }

    /**
     * Get subscription discount function.
     *
     * @param int $subscriptionId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return float
     */
    public function getSubscriptionDiscount($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        return $subscription->getDiscount();
    }

    /**
     * Get sub frequency function.
     *
     * @param int $subscriptionId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubFrequency($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        return $this->getSubscriptionFrequency($subscription->getFrequency());
    }

    /**
     * Get sub fee function.
     *
     * @param int $subscriptionId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return null|float
     */
    public function getSubFee($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        return $subscription->getFee();
    }

    /**
     * Get guest checkout function.
     *
     * @return int|void
     */
    public function getIsGuestCheckout()
    {
        $isLoggedIn = $this->customerSession->isLoggedIn();
        if ($isLoggedIn) {
            return true;
        }
        // check quote contain subscription items
        $subCount = 0;
        $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartData as $item) {
            if (1 == $item->getIsSubscribed()) {
                ++$subCount;
            } else {
                continue;
            }
        }
        if ($subCount > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get product details url.
     *
     * @param int $productId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return mixed
     */
    public function getProductDetailUrl($productId)
    {
        $productData = $this->productRepository->getById($productId);

        return $subConfig = $productData->getProductUrl();
    }

    /**
     * Get customer cancel function.
     *
     * @return mixed
     */
    public function getCanCustomerCancel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_CANCEL, $storeScope);
    }

    /**
     * Get customer pause function.
     *
     * @return mixed
     */
    public function getCanCustomerPause()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_PAUSE, $storeScope);
    }

    /**
     * Get customer address.
     *
     * @param int    $addressId
     * @param string $type
     *
     * @return mixed
     */
    public function getSubCustomerAddress($addressId, $type)
    {
        $formatType = $this->addressConfig->getFormatByCode($type);
        $address = $this->address->create()->load($addressId);
        $this->eventManager->dispatch('customer_address_format', ['type' => $formatType, 'address' => $address]);

        return $formatType->getRenderer()->renderArray($address->getData());
    }

    /**
     * Get customer address inline function.
     *
     * @param int $customerId
     *
     * @return array
     */
    public function getCustomerAddressInline($customerId)
    {
        $customerData = $this->customer->create()->load($customerId);
        $customerAddress = [];
        $customerAddress[0]['label'] = __('Please Select Address');
        $customerAddress[0]['value'] = '';

        foreach ($customerData->getAddresses() as $key => $address) {
            $customerAddress[$key]['label'] = $this->getSubCustomerAddress($address->getEntityId(), 'inline');
            $customerAddress[$key]['value'] = $address->getEntityId();
        }

        return $customerAddress;
    }

    /**
     * Get product subscribed function.
     *
     * @param int $product_id
     *
     * @return bool
     */
    public function getProductSubscribed($product_id)
    {
        $found = 0;
        $items = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getData('product_id') == $product_id && $item->getData('is_subscribed')) {
                $found = 1;

                break;
            }
        }

        return $found;
    }

    /**
     * Get card collection function.
     *
     * @param mixed $customerId
     *
     * @return array
     */
    public function getCardCollection($customerId)
    {
        $cardList = $this->paymentTokenManagement->getVisibleAvailableTokens($customerId);
        $details = [];
        foreach ($cardList as $key => $cl) {
            $maskedCC = json_decode($cl->getDetails(), true);
            $details[$key]['label'] = 'xxxx-'.$maskedCC['maskedCC'];
            $details[$key]['value'] = $cl->getPublicHash();
        }

        return $details;
    }

    /**
     * Get card function.
     *
     * @param mixed $customerId
     * @param mixed $publicHash
     *
     * @return array
     */
    public function getCard($customerId, $publicHash)
    {
        $details = [];
        $cardList = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);
        $cardTypes = $this->ccCongig->getCcAvailableTypes();
        $paymentTitle = $this->paymentHelper->getMethodInstance($cardList->getPaymentMethodCode());
        if ($cardList) {
            $cardDetails = $cardList->getTokenDetails();
            $a = json_decode($cardDetails, true);
            $details['cc_number'] = 'xxxx-'.$a['maskedCC'];
            foreach ($cardTypes as $code => $type) {
                if ($code == $a['type']) {
                    $details['cc_type'] = $type;
                }
            }
            $details['method_title'] = $paymentTitle->getTitle();

            return $details;
        }
    }

    /**
     * Get selected id function.
     *
     * @param int $id
     *
     * @return int
     */
    public function getSelectedId($id)
    {
        $add = $this->addressRepository->get($id);

        return $add->getCustomerAddressId();
    }

    /**
     * Subscription enable function.
     *
     * @return mixed
     */
    public function isSubscriptionEnableHowMany()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_HOWMANY, $storeScope);
    }

    /**
     * Get default subscription function.
     *
     * @param mixed $subscriptionId
     *
     * @return null|float
     */
    public function getDefaultSubscriptionHowMany($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);

        return $subscription->getHoWMany();
    }

    /**
     * Get subfrequency data function.
     *
     * @param mixed $subscriptionId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSubFrequencyData($subscriptionId)
    {
        return $this->getSubscriptionFrequency();
    }

    /**
     * Product custom price function.
     *
     * @param mixed $newQty
     * @param mixed $productId
     * @param mixed $downloadableLinks
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return float
     */
    public function getProductCustomPrice($newQty, $productId, $downloadableLinks)
    {
        $productData = $this->productRepository->getById($productId);
        $subId = $productData->getCustomAttribute('subscription_attribute_product')->getValue();
        $subscription = $this->subscriptionRepository->getById($subId);
        $discount = $subscription->getDiscount();
        $baseprice = $this->price->getBasePrice($productData, $newQty);
        if (!empty($downloadableLinks)) {
            $links = $this->getLinks($productId);
            foreach ($links as $link) {
                foreach ($downloadableLinks as $dw => $dlink) {
                    if ($link->getId() == $dlink) {
                        $baseprice += $link->getPrice();
                    }
                }
            }
        }
        if (self::FLOAT_VALUE == $baseprice || $baseprice <= $discount) {
            return $baseprice;
        }
        if (self::FLOAT_VALUE != $discount) {
            $newPrice = $baseprice - $discount;
        } else {
            $newPrice = $baseprice;
        }

        return $newPrice;
    }

    /**
     * Get product type function.
     *
     * @param mixed $productId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return null|string
     */
    public function getProductType($productId)
    {
        $productData = $this->productRepository->getById($productId);

        return $subConfig = $productData->getTypeId();
    }

    /**
     * Get product function.
     *
     * @param mixed $productId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     */
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Enter description here...
     *
     * @param mixed $productId
     *
     * @return array
     */
    public function getLinks($productId)
    {
        return $this->getProduct($productId)->getTypeInstance(true)
            ->getLinks($this->getProduct($productId))
        ;
    }
}
