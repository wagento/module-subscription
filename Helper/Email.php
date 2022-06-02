<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Helper;

use Magento\Tests\NamingConvention\true\mixed;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const TRANS_IDENT_EMAIL_NAME = 'trans_email/ident_%s/name';
    public const TRANS_IDENT_EMAIL = 'trans_email/ident_%s/email';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $temp_id;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customers;

    /**
     * @var \Wagento\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Customer\Model\CustomerFactory $customers
     * @param Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customers,
        Data $helper
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->customers = $customers;
        $this->helper = $helper;
    }

    /**
     * Return store configuration value of your template field that which id you set for template.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get store function.
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Get template id function.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Generate template function.
     *
     * @param mixed $emailTemplateVariables
     * @param mixed $senderInfo
     * @param mixed $receiverInfo
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return $this
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    // here you can defile area and store of template for which you prepare it
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
        ;

        return $this;
    }

    /**
     * Send reminder email function.
     *
     * @param mixed $emailTemplateVariables
     * @param mixed $senderInfo
     * @param mixed $receiverInfo
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function sentReminderEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->getStore()->getStoreId(), ];
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier('reminder_email_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo)
            ->getTransport()
        ;

        try {
            $result = $transport->sendMessage();
            if (!$result) {
                $response['success'] = true;
            }
            $this->inlineTranslation->resume();

            return $response;
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;

            return $response;
        }
    }

    /**
     * Status change email function.
     *
     * @param mixed $emailTemplateVariables
     * @param mixed $senderInfo
     * @param mixed $receiverInfo
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function sentStatusChangeEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        if (is_array($receiverInfo)) {
            $receiverInfo = $receiverInfo['email'];
        }

        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->getStore()->getStoreId(), ];
        $this->inlineTranslation->suspend();
        $transport = $this->_transportBuilder->setTemplateIdentifier('change_status_email_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo)
            ->getTransport()
        ;

        try {
            $result = $transport->sendMessage();
            if (!$result) {
                $response['success'] = true;
            }
            $this->inlineTranslation->resume();

            return $response;
        } catch (\Exception $e) {
            $response['error_msg'] = $e->getMessage();
            $response['error'] = true;

            return $response;
        }
    }

    /**
     * Email sender info array.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function getEmailSenderInfo($xmlPath)
    {
        $emailSender = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $namePath = sprintf(self::TRANS_IDENT_EMAIL_NAME, $emailSender);
        $emailPath = sprintf(self::TRANS_IDENT_EMAIL, $emailSender);

        $senderName = $this->getConfigValue($namePath, $this->getStore()->getStoreId());
        $senderEmail = $this->getConfigValue($emailPath, $this->getStore()->getStoreId());

        return [
            'name' => $senderName,
            'email' => $senderEmail,
        ];
    }

    /**
     * Receiver info function.
     *
     * @param mixed $customerId
     *
     * @return array
     */
    public function getRecieverInfo($customerId)
    {
        // Get customer by customerID
        $customer = $this->customers->create()->load($customerId);

        return [
            'name' => $customer->getFirstname().' '.$customer->getLastname(),
            'email' => $customer->getEmail(),
        ];
    }

    /**
     * Customer name function.
     *
     * @param mixed $customerId
     *
     * @return string
     */
    public function getCustomerName($customerId)
    {
        $customer = $this->customers->create()->load($customerId);

        return $customer->getFirstname().' '.$customer->getLastname();
    }

    /**
     * Status email variables function.
     *
     * @param mixed $id
     * @param mixed $status
     * @param mixed $customerId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return array
     */
    public function getStatusEmailVariables($id, $status, $customerId)
    {
        return [
            'store' => $this->getStore(),
            'increament_id' => $id,
            'customer_name' => $this->getCustomerName($customerId),
            'status' => $this->helper->getSubscriptionStatus($status),
        ];
    }

    /**
     * Email config enable function.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return mixed
     */
    public function getIsEmailConfigEnable($xmlPath)
    {
        return $this->getConfigValue($xmlPath, (int) $this->getStore());
    }

    /**
     * Status change email customer.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return bool
     */
    public function getIsStatusChangeEmailCustomer($xmlPath)
    {
        $emailOptions = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $arrayEmailOptions = explode(',', (string) $emailOptions);
        if (in_array(4, $arrayEmailOptions)) {
            return true;
        }

        return false;
    }

    /**
     * Status change email admin.
     *
     * @param mixed $xmlPath
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return bool
     */
    public function getIsStatusChangeEmailAdmin($xmlPath)
    {
        $emailOptions = $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
        $arrayEmailOptions = explode(',', (string) $emailOptions);
        if (in_array(5, $arrayEmailOptions)) {
            return true;
        }

        return false;
    }
}
