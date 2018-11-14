<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Model\Attribute\Backend;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
class Subscription extends AbstractBackend
{
    /**
     * @var \Wagento\Subscription\Model\SubscriptionRepository
     */
    protected $subscriptionRepository;
    /**
     * @var \Wagento\Subscription\Api\Data\SubscriptionInterfaceFactory
     */
    protected $subscriptionDataFactory;

    /**
     * Subscription constructor.
     * @param \Wagento\Subscription\Model\ProductRepository $subscriptionProductRepository
     * @param \Wagento\Subscription\Api\Data\ProductInterfaceFactory $subscriptionProductDataFactory
     */
    public function __construct(
        \Wagento\Subscription\Model\ProductRepository $subscriptionProductRepository,
        \Wagento\Subscription\Api\Data\ProductInterfaceFactory $subscriptionProductDataFactory
    ) {
        $this->subscriptionProductRepository = $subscriptionProductRepository;
        $this->subscriptionProductDataFactory = $subscriptionProductDataFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function afterSave($object)
    {
        $subscriptionProductRow = $this->subscriptionProductRepository
            ->getSubscriptionByProductId($object->getEntityId());
        $rowData = $subscriptionProductRow->getData();
        $subscriptionProductFactory = $this->subscriptionProductDataFactory->create();
        $subscriptionConfig = $object->getSubscriptionConfigurate();

        /*add subscription record in wagento_subscription_products*/
        if ($subscriptionConfig != 'no' &&  $subscriptionConfig != NULL) {
            if (!empty($rowData)) {
                $updateArray = [
                    'entity_id' => $rowData[0]['entity_id'],
                    'subscription_id' => $object->getSubscriptionAttributeProduct(),
                    'product_id' => $object->getEntityId(),
                ];
                $newRow = $subscriptionProductFactory->setData($updateArray);
            } else {
                $dataArray = [
                    'subscription_id' => $object->getSubscriptionAttributeProduct(),
                    'product_id' => $object->getEntityId(),
                ];
                $newRow = $subscriptionProductFactory->addData($dataArray);
            }
            $this->subscriptionProductRepository->save($newRow);
        }

        /*delete subscription record when subscription is set to no*/
        if ($subscriptionConfig == 'no' && !empty($rowData)) {
            $this->subscriptionProductRepository->deleteById($rowData[0]['entity_id']);
        }
        return $this;
    }
}
