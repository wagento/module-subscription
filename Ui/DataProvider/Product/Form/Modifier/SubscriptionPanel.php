<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Wagento\Subscription\Model\Subscription\Attribute\Source\SubscriptionList;

/**
 * Class SubscriptionPanel
 * @package Wagento\Subscription\Ui\DataProvider\Product\Form\Modifier
 */
class SubscriptionPanel extends AbstractModifier implements ModifierInterface
{
    /**
     * @var SubscriptionList
     */
    private $subscriptionList;

    /**
     * SubscriptionPanel constructor.
     * @param SubscriptionList $subscriptionList
     */
    public function __construct(SubscriptionList $subscriptionList)
    {
        $this->subscriptionList = $subscriptionList;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        // TODO: Implement modifyData() method.
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        $meta['subscription_configuration'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Subscription Options'),
                        'sortOrder' => 50,
                        'collapsible' => true,
                        'componentType' => 'fieldset'
                    ]
                ]
            ], 'children' => [
                'subscription_attribute_product' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'select',
                                'componentType' => 'field',
                                'options' => $this->subscriptionsOptions->toOptionArray(),
                                'visible' => 1,
                                'required' => 0,
                                'label' => __('Subscription Name')
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
