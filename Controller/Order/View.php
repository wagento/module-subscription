<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlInterface;

/**
 * Class View
 * @package Wagento\Subscription\Controller\Order
 */
class View extends \Magento\Sales\Controller\AbstractController\View implements OrderInterface
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * View constructor.
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $session
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $session
    ) {
        parent::__construct($context, $orderLoader, $resultPageFactory);
        $this->customerRepository = $customerRepository;
        $this->session = $session;
        $this->urlInterface = $context->getUrl();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        if ($this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomerId();
            $customerDataObject = $this->customerRepository->getById($customerId);
            $this->session->setCustomerData($customerDataObject);
        } else {
            $this->session->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $this->session->authenticate();
        }
        $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
        return $resultPage;
    }
}
