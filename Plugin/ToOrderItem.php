<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;

class ToOrderItem
{
    /**
     * @var Json
     */
    private $_serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     *  Around convert function.
     *
     * @param QuoteToOrderItem $subject
     * @param \Closure $proceed
     * @param mixed $item
     * @param mixed $data
     * @return mixed
     */
    public function aroundConvert(
        QuoteToOrderItem $subject,
        \Closure $proceed,
        $item,
        $data = []
    ) {
        $orderItem = $proceed($item, $data);
        $additionalOptions = $item->getOptionByCode('additional_options');
        if ($additionalOptions !== null) {
            $options = $orderItem->getProductOptions();
            // Set additional options to Order Item
            $options['additional_options'] = $this->_serializer->unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
        $orderItem->setIsSubscribed($item->getIsSubscribed());

        return $orderItem;
    }
}
