<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Sales\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscription_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Subscription Profile View'));
    }

    /**
     * @return WidgetTabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'subscription_info',
            [
                'label' => __('Information'),
                'title' => __('Information'),
                'content' => $this->getLayout()->createBlock(
                    'Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View\CustomerDetails'
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'subscription_edit',
            [
                'label' => __('Subscription Details'),
                'title' => __('Subscription Details'),
                'content' => $this->getLayout()->createBlock(
                    'Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View\SubscriptionDetails'
                )->toHtml(),
                'active' => false
            ]
        );
        return parent::_beforeToHtml();
    }
}
