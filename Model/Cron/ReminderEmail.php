<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Cron;

use Magento\Payment\Helper\Data as PaymentHelper;
use Wagento\Subscription\Helper\Email;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;

class ReminderEmail
{
    public const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER = 'braintree_subscription/email_config/email_sender';
    public const TRANS_IDENT_EMAIL_NAME = 'trans_email/ident_%s/name';
    public const TRANS_IDENT_EMAIL = 'trans_email/ident_%s/email';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Wagento\Subscription\Model\SubscriptionService
     */
    protected $subscriptionService;

    /**
     * @var \Wagento\Subscription\Model\SubscriptionSales
     */
    protected $subscriptionSales;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customers;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    private $addressRenderer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $date;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * ReminderEmail constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Wagento\Subscription\Model\SubscriptionService $subscriptionService
     * @param \Wagento\Subscription\Model\SubscriptionSales $subscriptionSales
     * @param Email $emailHelper
     * @param \Magento\Customer\Model\CustomerRegistry $customers
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateProcessor,
        \Psr\Log\LoggerInterface $logger,
        \Wagento\Subscription\Model\SubscriptionService $subscriptionService,
        \Wagento\Subscription\Model\SubscriptionSales $subscriptionSales,
        Email $emailHelper,
        \Magento\Customer\Model\CustomerRegistry $customers,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        PaymentHelper $paymentHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateProcessor = $dateProcessor;
        $this->logger = $logger;
        $this->subscriptionService = $subscriptionService;
        $this->subscriptionSales = $subscriptionSales;
        $this->emailHelper = $emailHelper;
        $this->customers = $customers;
        $this->orderRepository = $orderRepository;
        $this->addressRenderer = $addressRenderer;
        $this->date = $date;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Reminder email Cron function.
     */
    public function subscriptionReminderEmail()
    {
        $getIsEnable = $this->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
        $getIsSelectReminderEmail = $this->getIsSelectRemEmail(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS);
        if (1 == $getIsEnable && 1 == $getIsSelectReminderEmail) {
            return $this->getActiveSubscriptions();
        }
        $message = __('Cron reminder email configuration is disabled');
        $this->logger->info((string) $message);
    }

    /**
     * Get email config enable.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Tests\NamingConvention\true\mixed
     */
    public function getIsEmailConfigEnable($xmlPath)
    {
        return $this->emailHelper->getConfigValue($xmlPath, $this->emailHelper->getStore());
    }

    /**
     * Get email sender info function.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function getEmailSenderInfo($xmlPath)
    {
        $emailSender = $this->emailHelper->getConfigValue($xmlPath, $this->emailHelper->getStore()->getStoreId());
        $namePath = sprintf(self::TRANS_IDENT_EMAIL_NAME, $emailSender);
        $emailPath = sprintf(self::TRANS_IDENT_EMAIL, $emailSender);

        $senderName = $this->emailHelper->getConfigValue($namePath, $this->emailHelper->getStore()->getStoreId());
        $senderEmail = $this->emailHelper->getConfigValue($emailPath, $this->emailHelper->getStore()->getStoreId());

        return [
            'name' => $senderName,
            'email' => $senderEmail,
        ];
    }

    /**
     * Get receiver info function.
     *
     * @param mixed $customerId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function getReceiverInfo($customerId)
    {
        // Get customer by customerID
        $customer = $this->customers->retrieve($customerId);

        return [
            'name' => $customer->getFirstname().' '.$customer->getLastname(),
            'email' => $customer->getEmail(),
        ];
    }

    /**
     * Get active subscriptions function.
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return $this
     */
    protected function getActiveSubscriptions()
    {
        $now = $this->dateProcessor->date(null, null, false);

        $date = $now->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $nextDate = strtotime('+1 Day', strtotime($date));
        $subNextDate = $this->dateProcessor->date($nextDate);
        $subNextDateFormat = $subNextDate->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $subNextDateFormatEnd = $subNextDateFormat.' 23:59:59';
        $subNextDateFormat .= ' 00:00:00';

        $subscriptions = $this->collectionFactory->create();
        $subscriptions->addFieldToFilter('next_renewed', ['lteq' => $subNextDateFormatEnd])
            ->addFieldToFilter('next_renewed', ['gteq' => $subNextDateFormat])
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToFilter('how_many', ['neq' => 'billing_count'])
        ;

        if ($subscriptions->getSize() > 0) {
            $senderInfo = $this->getEmailSenderInfo(self::XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER);
            foreach ($subscriptions as $subscription) {
                try {
                    $customerId = $subscription->getCustomerId();
                    $subscriptionProductId = $subscription->getSubProductId();
                    $receiverInfo = $this->getReceiverInfo($customerId);

                    if (is_array($receiverInfo)) {
                        $receiverInfo = $receiverInfo['email'];
                    }

                    $order = $this->orderRepository->get($subscription->getSubscribeOrderId());
                    $order->setSubProductId($subscriptionProductId);
                    $nextRunDateFormat = $this->date
                        ->formatDate($subscription->getNextRenewed(), \IntlDateFormatter::LONG, false)
                    ;

                    // Assign values for your template variables
                    $emailTempVariables = [
                        'store' => $this->emailHelper->getStore(),
                        'customer_name' => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                        'date_of_next_run' => $nextRunDateFormat,
                        'formattedBillingAddress' => $this->addressRenderer
                            ->format($order->getBillingAddress(), 'html'),
                        'formattedShippingAddress' => $this->addressRenderer
                            ->format($order->getShippingAddress(), 'html'),
                        'payment_html' => $this->getPaymentHtml($order),
                        'shipping_description' => $order->getShippingDescription(),
                        'order' => $order,
                    ];

                    $result = $this->emailHelper->sentReminderEmail($emailTempVariables, $senderInfo, $receiverInfo);

                    if (isset($result['success'])) {
                        $message = __('Subscription Email sent Successfully %1'.$receiverInfo);
                        $this->logger->info((string) $message);
                    } elseif (isset($result['error'])) {
                        $message = __($result['error_msg']);
                        $this->logger->info((string) $message);
                    }
                } catch (\Exception $e) {
                    $this->logger->info((string) $e->getMessage());
                }
            }
        } else {
            $message = __('No collection found for the date %1 .', $subNextDateFormat);
            $this->logger->info((string) $message);
        }

        return $this;
    }

    /**
     * Get is select rem email function.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return bool
     */
    protected function getIsSelectRemEmail($xmlPath)
    {
        $emailOptions = $this->emailHelper->getConfigValue($xmlPath, $this->emailHelper->getStore()->getStoreId());
        $arrayEmailOptions = explode(',', (string) $emailOptions);
        if (in_array(1, $arrayEmailOptions)) {
            return true;
        }

        return false;
    }

    /**
     * Get payment html function.
     *
     * @param mixed $order
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getPaymentHtml($order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStoreId()
        );
    }
}
