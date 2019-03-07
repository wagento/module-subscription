<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Wagento\Subscription\Controller\RegistryConstants;

/**
 * Class Index
 * @package Wagento\Subscription\Controller\Adminhtml
 */
abstract class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Wagento_Subscription::manage';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \Wagento\Subscription\Api\SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;
    /**
     * @var \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory
     */
    protected $subscriptionDataFactory;

    /**
     * @var \Wagento\Subscription\Api\ProdcutRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Wagento\Subscription\Api\Data\ProductInterfaceFactory
     */
    protected $productDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;
    /**
     * @var \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Wagento\Subscription\Model\Subscription\Mapper
     */
    protected $subscriptionMapper;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wagento\Subscription\Api\SubscriptionRepositoryInterface $subscriptionRepository
     * @param \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory $subscriptionDataFactory
     * @param \Wagento\Subscription\Api\ProductRepositoryInterface $productRepository
     * @param \Wagento\Subscription\Api\Data\ProductInterfaceFactory $productDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Wagento\Subscription\Model\Subscription\Mapper $subscriptionMapper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Wagento\Subscription\Api\SubscriptionRepositoryInterface $subscriptionRepository,
        \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory $subscriptionDataFactory,
        \Wagento\Subscription\Api\ProductRepositoryInterface $productRepository,
        \Wagento\Subscription\Api\Data\ProductInterfaceFactory $productDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Wagento\Subscription\Model\Subscription\Mapper $subscriptionMapper
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->productRepository = $productRepository;
        $this->productDataFactory = $productDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->subscriptionMapper = $subscriptionMapper;
    }

    /**
     * Subscription initialization
     *
     * @return string subscription id
     */
    protected function initCurrentSubscription()
    {
        $subscriptionId = (int)$this->getRequest()->getParam('id');

        if ($subscriptionId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_SUBSCRIPTION_ID, $subscriptionId);
        }

        return $subscriptionId;
    }

    /**
     * Prepare subscriptions default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultSubscriptionTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Subscriptions'));
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }
}
