<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use function array_column;
use Magento\Backend\App\Action;
use function print_r;
use function var_dump;
use Wagento\Subscription\Controller\Adminhtml\Index as IndexAction;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Wagento\Subscription\Controller\Adminhtml\Index
 */
class MassDelete extends IndexAction
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $product;

    /**
     * MassDelete constructor.
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
     * @param \Magento\Catalog\Model\Product\Action $product
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
        \Wagento\Subscription\Model\Subscription\Mapper $subscriptionMapper,
        \Magento\Catalog\Model\Product\Action $product
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultForwardFactory,
            $coreRegistry,
            $subscriptionRepository,
            $subscriptionDataFactory,
            $productRepository,
            $productDataFactory,
            $dataObjectHelper,
            $filter,
            $collectionFactory,
            $resultJsonFactory,
            $logger,
            $subscriptionMapper
        );
        $this->product = $product;
    }

    /**
     * @var string
     */
    public $redirectUrl = '*/*/index';

    /**
     * Execute action
     *
     * @return $this|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $subscriptionsDeleted = 0;
            foreach ($collection->getData() as $key => $subscription) {
                $subscriptionId = $subscription['subscription_id'];
                $objSubscriptionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
                $productIds = array_column($objSubscriptionProducts->getData(), 'product_id');
                if(!empty($productIds)) {
                    foreach ($productIds as $key => $productId) {
                        //update product attributes
                        $storeId = 0;
                        $this->product->updateAttributes(
                            [$productId],
                            ['subscription_configurate' => 'no'],
                            $storeId
                        );

                        $this->product->updateAttributes(
                            [$productId],
                            ['subscription_attribute_product' => 'NULL'],
                            $storeId
                        );
                    }
                }
                $this->subscriptionRepository->deleteById($subscriptionId);
                $subscriptionsDeleted++;
            }
            if ($subscriptionsDeleted) {
                $this->messageManager->addSuccessMessage(__('A total of %1 
                record(s) were deleted.', $subscriptionsDeleted));
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('subscription/index/index');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
