<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Wagento\Subscription\Controller\Adminhtml\Index;

class Delete extends Index
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $product;

    /**
     * Delete constructor.
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
     *
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
     * Delete subscription action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $subscriptionId = $this->initCurrentSubscription();
        if (!empty($subscriptionId)) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            try {
                $objSubscriptionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
                $subscriptionIds = array_column($objSubscriptionProducts->getData(), 'subscription_id');
                if (empty($subscriptionIds)) {
                    $this->messageManager->addErrorMessage(__('The  subscription can\'t be deleted because of associated products'));
                    return $resultRedirect->setPath(
                        'subscription/index/edit',
                        ['id' => $subscriptionId]
                    );
                }
                $storeId = 0;
                //set subscription attribute value no to product
                if(!empty($objSubscriptionProducts->getData())) {
                    foreach ($objSubscriptionProducts as $subProducts) {
                        $this->product->updateAttributes([$subProducts->getProductId()],
                            ['subscription_configurate' => 'no'],$storeId);

                        $this->product->updateAttributes([$subProducts->getProductId()],
                            ['subscription_attribute_product' => 'NULL'],$storeId);
                    }
                }

                $this->subscriptionRepository->deleteById($subscriptionId);
                $this->messageManager->addSuccessMessage(__('You deleted the subscription.'));
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        return $resultRedirect->setPath('subscription/index/index');
    }
}
