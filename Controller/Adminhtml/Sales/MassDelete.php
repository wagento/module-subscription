<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Sales;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Wagento\Subscription\Api\SalesSubscriptionRepositoryInterface;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $redirectUrl = 'subscription/sales/index';

    /**
     * Massactions filter.
     *
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var SalesSubscriptionRepositoryInterface
     */
    protected $salesSubscriptionRepository;
    
    /**
     * MassDelete constructor function.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SalesSubscriptionRepositoryInterface $salesSubscriptionRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SalesSubscriptionRepositoryInterface $salesSubscriptionRepository
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->salesSubscriptionRepository = $salesSubscriptionRepository;
        parent::__construct($context);
    }

    /**
     * MassDelete execute function.
     *
     * @return $this|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $subscriptionsDeleted = 0;
            foreach ($collection->getAllIds() as $subscriptionId) {
                $this->salesSubscriptionRepository->deleteById($subscriptionId);
                ++$subscriptionsDeleted;
            }
            if ($subscriptionsDeleted) {
                $this->messageManager
                    ->addSuccessMessage(__('A total of %1 record(s) were deleted.', $subscriptionsDeleted))
                ;
            }
            $resultRedirect->setPath('subscription/sales/index');

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Check delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wagento_Subscription::subscription_grid');
    }
}
