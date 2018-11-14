<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Wagento\Subscription\Model\ResourceModel\SubscriptionSales\CollectionFactory;

class DataProvider extends AbstractDataProvider
{

    /**
     * DataProvider constructor.
     * @param  string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $salesSubCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $salesSubCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
    
        $this->collection = $salesSubCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }
}
