<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use function var_dump;
use Wagento\Subscription\Model\ProductFactory;
use Wagento\Subscription\Model\SubscriptionRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\CustomerFactory;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\AddressRepository;
use Magento\Catalog\Model\Product\Type\Price;

class Data extends AbstractHelper
{

    const XML_PATH_ENABLE_SUB = 'braintree_subscription/subscription_config/enable';
    const XML_PATH_SUBSCRIPTION_LABEL = 'braintree_subscription/subscription_config/product_option_label';
    const XML_PATH_SUBSCRIPTION_CANCEL = 'braintree_subscription/subscription_config/customer_cancel_subscription';
    const XML_PATH_SUBSCRIPTION_PAUSE = 'braintree_subscription/subscription_config/customer_pause_subscription';
    const XML_PATH_ENABLE_HOWMANY = 'braintree_subscription/subscription_config/customer_howmany_subscription';

    const FLOAT_VALUE = 0.0000;

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

    protected $customerSession;

    /**
     * Data constructor.
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
     * @param EncryptorInterface $encryptor
     * @param CcConfig $ccConfig
     * @param PaymentHelper $paymentHelper
     * @param AddressRepository $addressRepository
     * @param Price $price
     * @param \Magento\Downloadable\Model\Link $links
     * @param Magento\Customer\Model\Session $customerSession
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
        EncryptorInterface $encryptor,
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
        $this->encryptor = $encryptor;
        $this->ccCongig = $ccConfig;
        $this->paymentHelper = $paymentHelper;
        $this->addressRepository = $addressRepository;
        $this->price = $price;
        $this->links = $links;
        $this->customerSession = $customerSession;
    }

    /**
     * @return mixed
     */
    public function isSubscriptionModuleEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_SUB, $storeScope);
    }

    /**
     * @param $subFrequency
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionFrequency($subFrequency)
    {
        if ($subFrequency == 1) {
            return __("Daily");
        } elseif ($subFrequency == 2) {
            return __("Weekly");
        } elseif ($subFrequency == 3) {
            return __("Monthly");
        } elseif ($subFrequency == 4) {
            return __("Yearly");
        }
    }

    /**
     * @param $status
     * @return \Magento\Framework\Phrase
     */
    public function getSubscriptionStatus($status)
    {
        if ($status == '0') {
            return __('Canceled');
        } elseif ($status == '1') {
            return $status = __('Active');
        } elseif ($status == '2') {
            return $status = __('Paused');
        } elseif ($status == '3') {
            return $status = __('Completed');
        }
        return $status = __('N/A');
    }

    /**
     * @param $subFrequency
     * @return \Magento\Framework\Phrase
     */
    public function getHowManyUnits($subFrequency)
    {
        if ($subFrequency == 1) {
            return "Day(s)";
        } elseif ($subFrequency == 2) {
            return "Week(s)";
        } elseif ($subFrequency == 3) {
            return "Month(s)";
        } elseif ($subFrequency == 4) {
            return "Year(s)";
        }
    }

    /**
     * @param $additionalOptions
     * @return false|int|string
     */

    public function getSubscriptionHowMany($additionalOptions)
    {
        if (!empty($additionalOptions)) {
            $options = $this->_serializer->unserialize($additionalOptions);
            $howManyValue = $options['additional_options'][2]['value'];
            $howManyNumber = preg_replace("/[^0-9]/", '', $howManyValue);
            return $howManyNumber;
        } else {
            return __('N/A');
        }
    }

    /**
     * @return mixed
     */
    public function getProductOptionLabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_LABEL, $storeScope);
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getSubscriptionId($productId)
    {
        $subProduct = $this->subProductFactory->create()->getCollection()->addFieldToFilter('product_id', ['eq' => $productId]);
        return $subProduct->getFirstItem()->getSubscriptionId();
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionConfigurations($productId)
    {
        $productData = $this->productRepository->getById($productId);
        $subConfig = $productData->getCustomAttribute('subscription_configurate');
        if ($subConfig) {
            return $subConfig->getValue();
        }
        return ;
    }

    /**
     * @param $subscriptionId
     * @return null|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubName($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        return $subscription->getName();
    }

    /**
     * @param $subscriptionId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionDiscount($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        return $subscription->getDiscount();
    }

    /**
     * @param $subscriptionId
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubFrequency($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        return $this->getSubscriptionFrequency($subscription->getFrequency());
    }

    /**
     * @param $subscriptionId
     * @return float|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubFee($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        return $subscription->getFee();
    }

    /**
     * @return int|void
     */
    public function getIsGuestCheckout()
    {
        $isLoggedIn= $this->customerSession->isLoggedIn();
        if($isLoggedIn) {
            return true ;
        } else {
            //check quote contain subscription items
            $subCount = 0;
            $cartData = $this->checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($cartData as $item) {
                if ($item->getIsSubscribed() == 1) {
                    $subCount++;
                } else {
                    continue;
                }
            }
            if ($subCount > 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetailUrl($productId)
    {
        $productData = $this->productRepository->getById($productId);
        return $subConfig = $productData->getProductUrl();
    }

    /**
     * @return mixed
     */
    public function getCanCustomerCancel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_CANCEL, $storeScope);
    }

    /**
     * @return mixed
     */
    public function getCanCustomerPause()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIPTION_PAUSE, $storeScope);
    }

    /**
     * @param $addressId
     * @param $type
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
     * @param $customerId
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
     * @param $product_id
     * @return boolean
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
     * @return array
     */
    public function getCardCollection($customerId)
    {
        $cardList = $this->paymentTokenManagement->getVisibleAvailableTokens($customerId);
        $details = [];
        foreach ($cardList as $key => $cl) {
            $maskedCC = json_decode($cl->getDetails(), true);
            $details[$key]['label'] = 'xxxx-' . $maskedCC['maskedCC'];
            $details[$key]['value'] = $cl->getPublicHash();
        }
        return $details;
    }

    /**
     * @param $customerId
     * @param $publicHash
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
            $details['cc_number'] = 'xxxx-' . $a['maskedCC'];
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
     * @param $id
     * @return mixed
     */
    public function getSelectedId($id)
    {
        $add = $this->addressRepository->get($id);
        return $add->getCustomerAddressId();
    }

    /**
     * @return mixed
     */
    public function isSubscriptionEnableHowMany()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_HOWMANY, $storeScope);
    }

    /**
     * @param $subscriptionId
     * @return float|null
     */
    public function getDefaultSubscriptionHowMany($subscriptionId)
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId);
        return $subscription->getHoWMany();
    }

    /**
     * @param $subscriptionId
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubFrequencyData($subscriptionId)
    {
        return $this->getSubscriptionFrequency();
    }

    /**
     * @param $newQty
     * @param $productId
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        if ($baseprice == self::FLOAT_VALUE || $baseprice <= $discount) {
            return $baseprice;
        } else {
            if ($discount != self::FLOAT_VALUE) {
                $newPrice = $baseprice - $discount;
            } else {
                $newPrice = $baseprice;
            }
            return $newPrice;
        }
    }

    public function getProductType($productId)
    {
        $productData = $this->productRepository->getById($productId);
        return $subConfig = $productData->getTypeId();
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getLinks($productId)
    {
        return $this->getProduct($productId)->getTypeInstance(true)
            ->getLinks($this->getProduct($productId));
    }
}
