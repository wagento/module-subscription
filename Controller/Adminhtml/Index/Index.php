<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Wagento\Subscription\Controller\Adminhtml\Index as IndexAction;

class Index extends IndexAction
{
    /**
     * Subscription list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Wagento_Subscription::manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Subscriptions'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Manage Subscriptions'), __('Manage Subscriptions'));

        $this->_getSession()->unsSubscriptionData();
        $this->_getSession()->unsSubscriptionFormData();

        return $resultPage;
    }
}
