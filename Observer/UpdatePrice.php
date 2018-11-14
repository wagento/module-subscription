<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Wagento\Subscription\Helper\Data as subHelper;

class UpdatePrice implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var subHelper
     */
    protected $subHelper;

    /**
     * UpdatePrice constructor.
     * @param RequestInterface $request
     * @param Json $serializer
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param subHelper $subHelper
     */
    public function __construct(
        RequestInterface $request,
        Json $serializer,
        \Magento\Framework\Json\Helper\Data $helper,
        subHelper $subHelper
    ) {
    
        $this->_request = $request;
        $this->helper = $helper;
        $this->subHelper = $subHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $this->_request->getFullActionName();
        if ($action == 'subscription_ajax_subscribe' || $action == 'subscription_ajax_subscribecart') {
            $item = $observer->getEvent()->getData('quote_item');
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $subOptionsJson = $this->helper->jsonDecode($this->_request->getContent());
            $subOptions = $this->helper->jsonDecode($subOptionsJson);
            $subQty = $subOptions['subqty'];
            $subProductId = $subOptions['productId'];
            $downloadableLinks = $subOptions['links'];
            $setCustomPrice = $this->subHelper->getProductCustomPrice($subQty, $subProductId, $downloadableLinks);
            $item->setCustomPrice($setCustomPrice);
            $item->setOriginalCustomPrice($setCustomPrice);
            $item->setIsSubscribed(1);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}
