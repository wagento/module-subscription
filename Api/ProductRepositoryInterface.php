<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Api;

interface ProductRepositoryInterface
{
    /**
     * Create or update a Product.
     *
     * @param \Wagento\Subscription\Api\Data\ProductInterface $product
     * @return \Wagento\Subscription\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Wagento\Subscription\Api\Data\ProductInterface $product);

    /**
     * Get Product by Product ID.
     *
     * @param int $productId
     * @return \Wagento\Subscription\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If Product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($productId);

    /**
     * Get Product by Subscription ID.
     *
     * @param int $subscriptionId
     * @return \Wagento\Subscription\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySubscriptionId($subscriptionId);

    /**
     * Get Row by Product ID.
     *
     * @param int $productId
     * @return \Wagento\Subscription\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSubscriptionByProductId($productId);

    /**
     * Delete Product by Product ID.
     *
     * @param int $productId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productId);

    /**
     * Delete Product
     *
     * @param \Wagento\Subscription\Api\Data\ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Wagento\Subscription\Api\Data\ProductInterface $product);
}
