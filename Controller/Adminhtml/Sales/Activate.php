<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Sales;

use Wagento\Subscription\Model\SubscriptionSalesRepository;
use Magento\Framework\Controller\ResultFactory;
use Wagento\Subscription\Helper\Email;

/**
 * Class Activate
 */
class Activate extends \Magento\Backend\App\Action
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD_SENDER = 'braintree_subscription/email_config/email_sender';
    const XML_PATH_EMAIL_TEMPLATE_ENABLE = 'braintree_subscription/email_config/enable_email';
    const XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS = 'braintree_subscription/email_config/email_options';

    const SUB_STATUS_ACTIVE = 1;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

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
     * Activate constructor.
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
        $this->_resultPageFactory = $resultPageFactory;
        $this->subscriptionSalesRepository = $subscriptionSalesRepository;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $subProfileId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $getSubProfile = $this->subscriptionSalesRepository->getById($subProfileId);
            $customerId = $getSubProfile->getCustomerId();
            $getSubProfile->setStatus(self::SUB_STATUS_ACTIVE);
            $id = $getSubProfile->save()->getId();
            if ($id == $subProfileId) {
                $getIsEnable = $this->emailHelper->getIsEmailConfigEnable(self::XML_PATH_EMAIL_TEMPLATE_ENABLE);
                $getIsSelectChangeStatusEmail = $this->emailHelper->getIsStatusChangeEmailCustomer(self::XML_PATH_EMAIL_TEMPLATE_FIELD_EMAILOPTIONS);
                if ($getIsEnable && $getIsSelectChangeStatusEmail) {
                    //send email to customer
                    $emailTempVariables = $this->emailHelper->getStatusEmailVariables($subProfileId, self::SUB_STATUS_ACTIVE, $customerId);
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
                $this->messageManager->addSuccessMessage(__('Subscription Profile #%1 Activated Successfully.', $subProfileId));
            }
            $resultRedirect->setPath('subscription/sales/view', ['id' => $id]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('subscription/sales/view', ['id' => $id]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wagento_Subscription::subscription_grid');
    }
}
