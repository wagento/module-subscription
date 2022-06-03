<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use function get_class_methods;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use function print_r;
use function var_dump;
use Wagento\Subscription\Controller\Adminhtml\Index;

class Save extends Index
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $product;

    /**
     * @var \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid
     */
    protected $productGrid;

    /**
     * Save constructor.
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
     * @param \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid $productGrid
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
        \Magento\Catalog\Model\Product\Action $product,
        \Wagento\Subscription\Block\Adminhtml\Subscription\ProductGrid $productGrid
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
        $this->productGrid = $productGrid;
    }

    /**
     * Subscription Management Save
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $returnToEdit = false;
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        $subscription = $this->subscriptionDataFactory->create();
        $subscriptionId = $subscription->getSubscriptionId();

        if ($data) {
            try {
                if (empty($data['subscription_id'])) {
                    $data['subscription_id'] = null;
                }

                $this->dataObjectHelper->populateWithArray(
                    $subscription,
                    $data,
                    '\Wagento\Subscription\Api\Data\SubscriptionInterface::class'
                );

                $subscription = $this->subscriptionRepository->save($subscription);
                $subscriptionId = $subscription->getSubscriptionId();

                //save product in subscription
                $this->saveSubscriptionProducts($data, $subscriptionId);

                $this->_getSession()->unsSubscriptionFormData();

                $this->messageManager->addSuccessMessage(__('You saved the subscription.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
                $this->_getSession()->setSubscriptionFormData($data);
                $returnToEdit = true;
            }
        }

        if ($returnToEdit) {
            if ($subscriptionId) {
                $resultRedirect->setPath(
                    'subscription/*/edit',
                    ['id' => $subscriptionId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'subscription/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('subscription/index');
        }
        return $resultRedirect;
    }

    /**
     * Save subscription products.
     *
     * @param array $data
     * @param int $subscriptionId
     * @return void
     */
    private function saveSubscriptionProducts($data, $subscriptionId)
    {
        try {
            if (!isset($data['category_products'])) {
                return;
            }

            $productSaved = array_keys(json_decode($this->productGrid->getProductsJson($subscriptionId), true));
            $productIds = array_keys(json_decode($data['category_products'], true));

            $deletedIds = array_diff($productSaved, $productIds);

            $dataSubscriptionProduct = ['subscription_id' => $subscriptionId];

            $objSubscriptionProducts = $this->productRepository->getBySubscriptionId($subscriptionId);
            $subscriptionIds = array_column($objSubscriptionProducts->getData(), 'subscription_id');

            if (count($deletedIds)) {
                foreach ($deletedIds as $deletedId) {
                    $storeId = 0;
                    $this->product->updateAttributes(
                        [$deletedId],
                        ['subscription_attribute_product' => 'NULL'],
                        $storeId
                    );

                    $this->product->updateAttributes(
                        [$deletedId],
                        ['subscription_configurate' => 'no'],
                        $storeId
                    );
                }
            }

            if (count($productIds) > 0) {
                foreach ($productIds as $productId) {
                    if (!in_array($productId, $subscriptionIds)) {

                        $storeId = 0;
                        $this->product->updateAttributes(
                            [$productId],
                            ['subscription_attribute_product' => $subscriptionId],
                            $storeId
                        );

                        $storeId = 0;
                        $this->product->updateAttributes(
                            [$productId],
                            ['subscription_configurate' => 'optional'],
                            $storeId
                        );
                    }

                    $subscriptionProduct = $this->productDataFactory->create();
                    $dataSubscriptionProduct['product_id'] = $productId;
                    $this->dataObjectHelper->populateWithArray(
                        $subscriptionProduct,
                        $dataSubscriptionProduct,
                        '\Wagento\Subscription\Api\Data\ProductInterface::class'
                    );
                    $this->productRepository->save($subscriptionProduct);
                }
            }

            if (count($subscriptionIds) > 0) {
                foreach ($objSubscriptionProducts as $objSubscriptionProduct) {
                    if (!in_array($objSubscriptionProduct->getSubcriptionId(), $productIds)) {
                        $this->productRepository->delete($objSubscriptionProduct);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
            $this->_getSession()->setSubscriptionFormData($data);
        }
    }
}
