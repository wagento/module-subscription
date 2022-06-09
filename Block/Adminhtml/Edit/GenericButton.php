<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Edit;

use Wagento\Subscription\Controller\RegistryConstants;

class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Return the subscription Id.
     *
     * @return null|int
     */
    public function getSubscriptionId()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_SUBSCRIPTION_ID);
    }

    /**
     * Generate url by route and parameters.
     *
     * @param string $route
     * @param array  $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
