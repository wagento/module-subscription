<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Ui\Component\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Wagento\Subscription\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;
    /**
     * @var \Wagento\Subscription\Model\ResourceModel\Subscription
     */
    protected $subscriptionResource;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\Registry $registry
     * @param \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory
     * @param \Wagento\Subscription\Model\ResourceModel\Subscription $subscriptionResource
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        \Magento\Framework\Registry $registry,
        \Wagento\Subscription\Model\ResourceModel\Subscription\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Wagento\Subscription\Model\SubscriptionFactory $subscriptionFactory,
        \Wagento\Subscription\Model\ResourceModel\Subscription $subscriptionResource,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $meta = [],
        array $data = []
    ) {

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->registry = $registry;
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->session = $session;
        $this->messageManager = $messageManager;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $subscription) {
            $this->loadedData[$subscription->getSubscriptionId()] = $subscription->getData();
        }

        $data = $this->session->getSubscriptionFormData();
        if (!empty($data)) {
            $subscriptionId = isset($data['subscription']['subscription_id']) ? $data['subscription']['subscription_id'] : null;
            $this->loadedData[$subscriptionId] = $data;
            $this->session->unsSubscriptionFormData();
        }

        return $this->loadedData;
    }
}
