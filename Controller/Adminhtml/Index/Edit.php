<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Magento\Framework\Exception\NoSuchEntityException;
use Wagento\Subscription\Controller\Adminhtml\Index;

class Edit extends Index
{
    /**
     * Edit execute function.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $subscriptionId = $this->initCurrentSubscription();

        $subscriptionData = [];
        $subscription = null;
        $isExistingSubscription = (bool) $subscriptionId;
        if ($isExistingSubscription) {
            try {
                $subscription = $this->subscriptionRepository->getById($subscriptionId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager
                    ->addExceptionMessage($e, __('Something went wrong while editing the subscription.'))
                ;
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('subscription/*/index');

                return $resultRedirect;
            }
        }
        $subscriptionData['subscription_id'] = $subscriptionId;
        $this->_getSession()->setSubscriptionData($subscriptionData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Wagento_Subscription::manage');
        $this->prepareDefaultSubscriptionTitle($resultPage);
        $resultPage->setActiveMenu('Wagento_Subscription::subscription');
        if ($isExistingSubscription) {
            $resultPage->getConfig()->getTitle()->prepend($subscription->getName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Subscription'));
        }

        return $resultPage;
    }
}
