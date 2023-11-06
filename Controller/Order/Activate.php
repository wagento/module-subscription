<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException;
use Psr\Log\LoggerInterface;
use Wagento\Subscription\Helper\Email;
use Wagento\Subscription\Model\SubscriptionSalesRepository;

class Activate extends Action
{
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_RECIEVER = 'braintree_subscription/email_config/email_reciever';
    public const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    public const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';

    public const SUB_STATUS_ACTIVATE = 1;

    /**
     * @var SubscriptionSalesRepository
     */
    protected $subSalesRepository;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Activate constructor.
     *
     * @param Context $context
     * @param SubscriptionSalesRepository $subSalesRepository
     * @param Email $emailHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context                     $context,
        SubscriptionSalesRepository $subSalesRepository,
        Email                       $emailHelper,
        LoggerInterface             $logger
    )
    {
        parent::__construct($context);
        $this->subSalesRepository = $subSalesRepository;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * Activate execute function.
     *
     * @return ResponseInterface|ResultInterface
     * @throws NoSuchEntityException
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $subSales = $this->subSalesRepository->getById($data['order_id']);
            $subSales->setStatus(self::SUB_STATUS_ACTIVATE);
            $customerId = $subSales->getCustomerId();
            $id = $subSales->save()->getId();
            $getIsEnable = $this->emailHelper->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
            $getIsSelectChangeStatusEmail = $this->emailHelper
                ->getIsStatusChangeEmailAdmin(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS);
            if (1 == $getIsEnable && 1 == $getIsSelectChangeStatusEmail) {
                // send email to customer
                $emailTempVariables = $this->emailHelper
                    ->getStatusEmailVariables($id, self::SUB_STATUS_ACTIVATE, $customerId);
                $receiverInfo = $this->emailHelper->getEmailSenderInfo(self::XML_PATH_EMAIL_TEMPLATE_FIELD_RECIEVER);
                $senderInfo = $this->emailHelper->getRecieverInfo($customerId);
                $result = $this->emailHelper->sentStatusChangeEmail($emailTempVariables, $senderInfo, $receiverInfo);

                if (isset($result['success'])) {
                    $message = __('Subscription Status Change Email Sent Successfully %1' . $receiverInfo['email']);
                    $this->logger->info((string)$message);
                } elseif (isset($result['error'])) {
                    $message = __($result['error_msg']);
                    $this->logger->info((string)$message);
                }
                $this->messageManager->addSuccessMessage(__('Subscription Profile #%1 Reactivated Successfully.', $id));
            }
        } catch (CouldNotSaveException $ex) {
            $this->messageManager->addErrorMessage(__($ex->getMessage()));
        }

        return $resultRedirect->setPath('subscription/order/view', ['order_id' => $data['order_id']]);
    }
}
