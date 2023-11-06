<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Wagento\Subscription\Helper\Email;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;
use Wagento\Subscription\Model\SubscriptionSalesFactory;
use Wagento\Subscription\Model\SubscriptionService;

class Bill
{
    public const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER = 'braintree_subscription/email_config/email_sender';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $dateProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    /**
     * @var \Wagento\Subscription\Model\SubscriptionSales
     */
    protected $subscriptionSales;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * Bill constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $dateProcessor
     * @param LoggerInterface $logger
     * @param SubscriptionService $subscriptionService
     * @param SubscriptionSalesFactory $subscriptionSales
     * @param ResourceConnection $resource
     * @param Email $emailHelper
     */
    public function __construct(
        CollectionFactory        $collectionFactory,
        TimezoneInterface        $dateProcessor,
        LoggerInterface          $logger,
        SubscriptionService      $subscriptionService,
        SubscriptionSalesFactory $subscriptionSales,
        ResourceConnection       $resource,
        Email                    $emailHelper
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->dateProcessor = $dateProcessor;
        $this->logger = $logger;
        $this->subscriptionService = $subscriptionService;
        $this->subscriptionSales = $subscriptionSales;
        $this->resource = $resource;
        $this->emailHelper = $emailHelper;
    }

    /**
     * Run subscription cron.
     */
    public function runSubscriptionCron()
    {
        $this->getActiveSubscriptions();
    }

    /**
     * Run due subscriptions (single mode).
     *
     * @return $this
     */
    protected function getActiveSubscriptions()
    {
        $now = $this->dateProcessor->date(null, null, false);
        $currentDate = $now->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $subscriptions = $this->collectionFactory->create();
        $connection = $this->resource->getConnection();
        $vaultPaymentToken = $connection->getTableName('vault_payment_token_order_payment_link');
        $currentDateEnd = $currentDate . ' 23:59:59';
        $currentDate .= ' 00:00:00';
        $subscriptions->getSelect()->join(
            $vaultPaymentToken . ' as pt',
            'main_table.subscribe_order_id = pt.order_payment_id',
            ['payment_token_id']
        );
        $subscriptions->addFieldToFilter('next_renewed', ['lteq' => $currentDateEnd])
            ->addFieldToFilter('next_renewed', ['gteq' => $currentDate])
            ->addFieldToFilter('status', ['eq' => 1])
            ->addFieldToFilter('how_many', ['neq' => 'billing_count']);

        if ($subscriptions->getSize() > 0) {
            foreach ($subscriptions as $subscription) {
                try {
                    $result = $this->subscriptionService->generateOrder([$subscription]);
                    if (isset($result['success'])) {
                        try {
                            $nextRenewedDate = $result['success_data']['next_renewed'];

                            $salesSubModel = $this->subscriptionSales->create()->load($subscription->getId());
                            $billingCount = $salesSubModel->getBillingCount() + 1;
                            $salesSubModel->setBillingCount($billingCount);
                            $salesSubModel->setLastRenewed($currentDate);
                            $salesSubModel->setNextRenewed($nextRenewedDate);
                            $salesSubModel->save();

                            // Set Status COmpleted when Billing = How Many
                            $getBillingCount = $salesSubModel->getBillingCount();
                            $getHowMany = $salesSubModel->getHowMany();
                            if ($getBillingCount == $getHowMany) {
                                $this->getActiveSubscriptionsResult($salesSubModel, $subscription);
                            }
                            $message = __('Subscription order
                             placed Successfully order#%1 .', $result['success_data']['increment_id']);
                            $this->logger->info((string)$message);
                        } catch (\Exception $e) {
                            $this->logger->info((string)$e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->info((string)$e->getMessage());
                }
            }
        } else {
            $message = __('No collection found for the date %1 .', $currentDate);
            $this->logger->info((string)$message);
        }

        return $this;
    }

    /**
     * Get active subscription result.
     *
     * @param $salesSubModel
     * @param $subscription
     * @throws NoSuchEntityException
     */
    private function getActiveSubscriptionsResult($salesSubModel, $subscription): void
    {
        $status = 3;
        $customerId = $salesSubModel->getCustomerId();
        $salesSubModel->setStatus($status);
        $salesSubModel->save();

        $getIsEnable = $this->emailHelper
            ->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
        $getIsSelectChangeStatusEmail = $this->emailHelper
            ->getIsStatusChangeEmailCustomer(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS);

        if (1 == $getIsEnable && 1 == $getIsSelectChangeStatusEmail) {
            // send email to customer that subscription cycle completed
            $emailTempVariables = $this->emailHelper
                ->getStatusEmailVariables($subscription->getId(), $status, $customerId);
            $senderInfo = $this->emailHelper
                ->getEmailSenderInfo(self::XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER);
            $receiverInfo = $this->emailHelper->getRecieverInfo($customerId);

            $result = $this->emailHelper
                ->sentStatusChangeEmail($emailTempVariables, $senderInfo, $receiverInfo);

            if (isset($result['success'])) {
                $message = __('Subscription Status Change Email Sent Successfully %1' . $receiverInfo['email']);
                $this->logger->info((string)$message);
            } elseif (isset($result['error'])) {
                $message = __($result['error_msg']);
                $this->logger->info((string)$message);
            }
        } else {
            $message = __('Email configuration is disabled');
            $this->logger->info((string)$message);
        }

    }
}
