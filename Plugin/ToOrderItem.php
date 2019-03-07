<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Plugin;

use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ToOrderItem
 * @package Wagento\Subscription\Plugin
 */
class ToOrderItem
{
    /**
     * @var
     */
    private $_serializer;

    /**
     * ToOrderItem constructor.
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * aroundConvert
     *
     * @param QuoteToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $data
     *
     * @return \Magento\Sales\Model\Order\Item
     */

    public function aroundConvert(
        QuoteToOrderItem $subject,
        \Closure $proceed,
        $item,
        $data = []
    ) {
    
        $orderItem = $proceed($item, $data);
        $additionalOptions = $item->getOptionByCode('additional_options');
        if (!is_null($additionalOptions)) {
            $options = $orderItem->getProductOptions();
            // Set additional options to Order Item
            $options['additional_options'] = $this->_serializer->unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
        $orderItem->setIsSubscribed($item->getIsSubscribed());
        return $orderItem;
    }
}
