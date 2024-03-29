<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Sales;

use Magento\Framework\Controller\ResultFactory;
use Wagento\Subscription\Helper\Email;
use Wagento\Subscription\Model\SubscriptionSalesRepository;

class Cancel extends \Magento\Backend\App\Action
{
    protected const XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER = 'braintree_subscription/email_config/email_sender';
    protected const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    protected const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';
    protected const SUB_STATUS_CANCEL = 0;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SubscriptionSalesRepository
     */
    protected $subscriptionSalesRepository;

    /**
     * @var Email
     */
    protected $emailHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Cancel constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param SubscriptionSalesRepository $subscriptionSalesRepository
     * @param Email $emailHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        SubscriptionSalesRepository $subscriptionSalesRepository,
        Email $emailHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriptionSalesRepository = $subscriptionSalesRepository;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * Cancel execute function.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $subProfileId = $this->getRequest()->getParam('id');

        try {
            $getSubProfile = $this->subscriptionSalesRepository->getById($subProfileId);
            $getSubProfile->setStatus(self::SUB_STATUS_CANCEL);
            $customerId = $getSubProfile->getCustomerId();
            $id = $getSubProfile->save();
            if ($id == $getSubProfile) {
                $getIsEnable = $this->emailHelper->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
                $getIsSelectChangeStatusEmail = $this->emailHelper
                    ->getIsStatusChangeEmailCustomer(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS)
                ;
                if ($getIsEnable && $getIsSelectChangeStatusEmail) {
                    // send email to customer
                    $emailTempVariables = $this->emailHelper
                        ->getStatusEmailVariables($subProfileId, self::SUB_STATUS_CANCEL, $customerId)
                    ;
                    $senderInfo = $this->emailHelper->getEmailSenderInfo(self::XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER);
                    $receiverInfo = $this->emailHelper->getRecieverInfo($customerId);
                    $result = $this->emailHelper
                        ->sentStatusChangeEmail($emailTempVariables, $senderInfo, $receiverInfo)
                    ;
                    if (isset($result['success'])) {
                        $message = __('Subscription Status Change Email Sent Successfully %1'.$receiverInfo['email']);
                        $this->logger->info((string) $message);
                    } elseif (isset($result['error'])) {
                        $message = __($result['error_msg']);
                        $this->logger->info((string) $message);
                    }
                } else {
                    $message = __('Email configuration is disabled');
                    $this->logger->info((string) $message);
                }
                $this->messageManager
                    ->addSuccessMessage(__('Subscription Profile #%1 Canceled Successfully.', $subProfileId))
                ;
            }
            $resultRedirect->setPath('subscription/sales/view', ['id' => $subProfileId]);

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('subscription/sales/view', ['id' => $subProfileId]);
        }
    }

    /**
     * Is allowed the module function.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wagento_Subscription::subscription_grid');
    }
}
