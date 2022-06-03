<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Wagento\Subscription\Api\Data\SubscriptionInterface;
use Wagento\Subscription\Controller\Adminhtml\Index as IndexAction;

class InlineEdit extends IndexAction
{
    /**
     * @var SubscriptionInterface
     */
    protected $subscription;

    /**
     * Inlineedit execute function.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $subscriptionId) {
            $this->setSubscription($this->subscriptionRepository->getById($subscriptionId));
            $this->updateSubscription($postItems[$subscriptionId]);
            $this->saveSubscription($this->getSubscription());
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists(),
        ]);
    }

    /**
     * Update subscription data.
     *
     * @param array $data
     * @return void
     */
    protected function updateSubscription(array $data)
    {
        $subscription = $this->getSubscription();
        $subscriptionData = array_merge(
            $this->subscriptionMapper->toFlatArray($subscription),
            $data
        );
        $this->dataObjectHelper->populateWithArray(
            $subscription,
            $subscriptionData,
            '\Wagento\Subscription\Api\Data\SubscriptionInterface::class'
        );
    }

    /**
     * Save subscription with error catching.
     *
     * @param SubscriptionInterface $subscription
     * @return void
     */
    protected function saveSubscription(SubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionRepository->save($subscription);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()->addErrorMessage($this->getErrorWithCustomerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addErrorMessage($this->getErrorWithCustomerId($e->getMessage()));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($this->getErrorWithCustomerId('We can\'t save the customer.'));
            $this->logger->critical($e);
        }
    }

    /**
     * Set subscription.
     *
     * @param SubscriptionInterface $subscription
     * @return $this
     */
    protected function setSubscription(SubscriptionInterface $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Receive subscription.
     *
     * @return SubscriptionInterface
     */
    protected function getSubscription()
    {
        return $this->subscription;
    }
}
