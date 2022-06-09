<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerDetail implements OptionSourceInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private $cmsPageCollectionFactory;

    /**
     * CustomerDetail constructor.
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsPageCollectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsPageCollectionFactory
    ) {
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
    }

    /**
     * Get Grid row type array for option element.
     *
     * @return array
     */
    public function getOptions()
    {
        $collection = $this->cmsPageCollectionFactory->create();
        $options = [];
        foreach ($collection as $cms) {
            $options[] = ['label' => $cms->getTitle(), 'value' => $cms->getId()];
        }
        return $options;
    }

    /**
     * Option array function.
     *
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
