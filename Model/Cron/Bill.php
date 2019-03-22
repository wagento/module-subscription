<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Cron;

use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSalesFactory;
use Wagento\Subscription\Helper\Email;

/**
 * Class Bill
 * @package Wagento\Subscription\Model\Cron
 */
class Bill
{
    const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';
    const XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER = 'braintree_subscription/email_config/email_sender';

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
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * Bill constructor.
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Wagento\Subscription\Model\SubscriptionService $subscriptionService
     * @param \Wagento\Subscription\Model\SubscriptionSalesFactory $subscriptionSales
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Email $emailHelper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateProcessor,
        \Psr\Log\LoggerInterface $logger,
        \Wagento\Subscription\Model\SubscriptionService $subscriptionService,
        \Wagento\Subscription\Model\SubscriptionSalesFactory $subscriptionSales,
        \Magento\Framework\App\ResourceConnection $resource,
        Email $emailHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateProcessor = $dateProcessor;
        $this->logger = $logger;
        $this->subscriptionService = $subscriptionService;
        $this->subscriptionSales = $subscriptionSales;
        $this->resource = $resource;
        $this->emailHelper = $emailHelper;
    }

    /**
     * Run subscription cron
     */
    public function runSubscriptionCron()
    {
        $this->getActiveSubscriptions();
    }

    /**
     * Run due subscriptions (single mode)
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
        $currentDateEnd = $currentDate. ' 23:59:59';
        $currentDate.= ' 00:00:00';
        $subscriptions->getSelect()->join(
            $vaultPaymentToken . ' as pt',
            "main_table.subscribe_order_id = pt.order_payment_id",
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

                            /*Set Status COmpleted when Billing = How Many*/
                            $getBillingCount = $salesSubModel->getBillingCount();
                            $getHowMany = $salesSubModel->getHowMany();

                            if ($getBillingCount == $getHowMany) {
                                $status = 3;
                                $customerId = $salesSubModel->getCustomerId();
                                $salesSubModel->setStatus($status);
                                $salesSubModel->save();

                                $getIsEnable = $this->emailHelper->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
                                $getIsSelectChangeStatusEmail = $this->emailHelper->getIsStatusChangeEmailCustomer(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS);
                                if ($getIsEnable == 1 && $getIsSelectChangeStatusEmail == 1) {
                                    /*send email to customer that subscription cycle completed */
                                    $emailTempVariables = $this->emailHelper->getStatusEmailVariables($subscription->getId(), $status, $customerId);
                                    $senderInfo = $this->emailHelper->getEmailSenderInfo(self::XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER);
                                    $receiverInfo = $this->emailHelper->getRecieverInfo($customerId);
                                    $result = $this->emailHelper->sentStatusChangeEmail($emailTempVariables, $senderInfo, $receiverInfo);
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
                            $message = __('Subscription order placed Successfully order#%1 .', $result['success_data']['increment_id']);
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
}
