<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Index;

use Wagento\Subscription\Controller\Adminhtml\Index;

/**
 * Class NewAction
 * @package Wagento\Subscription\Controller\Adminhtml\Index
 */
class NewAction extends Index
{
    /**
     * Create new subscriptions action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;
    }
}
