<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Wagento\Subscription\Api\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;
    /**
     * @var ResourceModel\Subscription
     */
    protected $subscriptionResource;

    /**
     * SubscriptionRepository constructor.
     * @param SubscriptionFactory $subscriptionFactory
     * @param ResourceModel\Subscription $subscriptionResource
     */
    public function __construct(
        \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory,
        \Wagento\Subscription\Model\ResourceModel\Subscription $subscriptionResource
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Wagento\Subscription\Api\Data\SubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionResource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the subscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $subscription;
    }

    /**
     * @param int $subscriptionId
     * @return \Wagento\Subscription\Api\Data\SubscriptionInterface|Subscription
     * @throws NoSuchEntityException
     */
    public function getById($subscriptionId)
    {
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, $subscriptionId);
        if (!$subscription->getSubscriptionId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }
        return $subscription;
    }

    /**
     * @param int $subscriptionId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($subscriptionId)
    {
        return $this->delete($this->getById($subscriptionId));
    }

    /**
     * @param \Wagento\Subscription\Api\Data\SubscriptionInterface $subscription
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Wagento\Subscription\Api\Data\SubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionResource->delete($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the subscription: %1', $exception->getMessage()));
        }
        return true;
    }
}
