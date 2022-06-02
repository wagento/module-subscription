<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Wagento\Subscription\Api\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Wagento\Subscription\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * ProductRepository constructor.
     * @param ProductFactory $productFactory
     * @param ResourceModel\Product $productResource
     * @param ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Wagento\Subscription\Model\ProductFactory $productFactory,
        \Wagento\Subscription\Model\ResourceModel\Product $productResource,
        \Wagento\Subscription\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {

        $this->productFactory = $productFactory;
        $this->productResource = $productResource;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Save data function.
     *
     * @param \Wagento\Subscription\Api\Data\ProductInterface $product
     * @return \Wagento\Subscription\Api\Data\ProductInterface
     * @throws CouldNotSaveException
     */
    public function save(\Wagento\Subscription\Api\Data\ProductInterface $product)
    {
        try {
            $this->productResource->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Product: %1', $exception->getMessage()),
                $exception
            );
        }

        return $product;
    }

    /**
     * Get product id function.
     *
     * @param int $productId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($productId)
    {
        $product = $this->productFactory->create();
        $this->productResource->load($product, $productId);
        if (!$product->getProductId()) {
            throw new NoSuchEntityException(__('Product with id "%1" does not exist.', $productId));
        }
        return $product;
    }

    /**
     * Get subscription id function.
     *
     * @param int $subscriptionId
     * @return mixed
     */
    public function getBySubscriptionId($subscriptionId)
    {
        return $this->_productCollectionFactory->create()->addFieldToFilter('subscription_id', $subscriptionId);
    }

    /**
     * Get subscritpion product id function.
     *
     * @param int $productId
     * @return mixed
     */
    public function getSubscriptionByProductId($productId)
    {
        return $this->_productCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId);
    }

    /**
     * Delete product id function.
     *
     * @param int $productId
     * @return bool
     */
    public function deleteById($productId)
    {
        return $this->delete($this->getById($productId));
    }

    /**
     * Delete Page
     *
     * @param \Wagento\Subscription\Api\Data\ProductInterface $product
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Wagento\Subscription\Api\Data\ProductInterface $product)
    {
        try {
            $this->productResource->delete($product);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Product: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
}
