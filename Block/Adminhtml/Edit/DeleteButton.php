<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button function.
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Delete Subscription'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\''.__(
                'Are you sure you want to do this?'
            ).'\', \''.$this->getDeleteUrl().'\')',
            'sort_order' => 20,
        ];
    }

    /**
     * Get delete url function.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getSubscriptionId()]);
    }
}
