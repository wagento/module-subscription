<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Wagento\Subscription\Api\SalesSubscriptionRepositoryInterface;

class SubscriptionSalesRepository implements SalesSubscriptionRepositoryInterface
{
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionSalesFactory;

    /**
     * @var ResourceModel\Subscription
     */
    protected $subscriptionSalesResource;

    /**
     * SubscriptionSalesRepository constructor.
     *
     * @param SubscriptionSalesFactory $subscriptionSalesFactory
     * @param ResourceModel\SubscriptionSales $subscriptionSalesResource
     */
    public function __construct(
        SubscriptionSalesFactory $subscriptionSalesFactory,
        ResourceModel\SubscriptionSales $subscriptionSalesResource
    ) {
        $this->subscriptionSalesFactory = $subscriptionSalesFactory;
        $this->subscriptionSalesResource = $subscriptionSalesResource;
    }

    /**
     * Save subscription function.
     *
     * @param \Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscription
     * @return \Wagento\Subscription\Api\Data\SalesSubscriptionInterface
     * @throws CouldNotSaveException
     */
    public function save(\Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionSalesResource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the subscription: %1', $exception->getMessage()),
                $exception
            );
        }

        return $subscription;
    }

    /**
     * Get subscription id function.
     *
     * @param int $subscriptionId
     *
     * @throws NoSuchEntityException
     *
     * @return Subscription|SubscriptionSales|\Wagento\Subscription\Api\Data\SalesSubscriptionInterface
     */
    public function getById($subscriptionId)
    {
        $subscription = $this->subscriptionSalesFactory->create();
        $this->subscriptionSalesResource->load($subscription, $subscriptionId);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }

        return $subscription;
    }

    /**
     * Delete subscription id function.
     *
     * @param int $subscriptionSalesId
     *
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     *
     * @return bool
     */
    public function deleteById($subscriptionSalesId)
    {
        return $this->delete($this->getById($subscriptionSalesId));
    }

    /**
     * Delete Page.
     *
     * @param \Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscriptionSales
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Wagento\Subscription\Api\Data\SalesSubscriptionInterface $subscriptionSales)
    {
        try {
            $this->subscriptionSalesResource->delete($subscriptionSales);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the subscription: %1',
                $exception->getMessage()
            ));
        }

        return true;
    }
}
