<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Subscription\Model\Attribute\Backend;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
/**
 * Class Subscription
 * @package Wagento\Subscription\Model\Attribute\Backend
 */
class Subscription extends AbstractBackend
{
    /**
     * @var \Wagento\Subscription\Model\ProductRepository
     */
    protected $subscriptionProductRepository;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Subscription constructor.
     * @param \Wagento\Subscription\Model\ProductRepository $subscriptionProductRepository

     */
    public function __construct(
        \Wagento\Subscription\Model\ProductRepository $subscriptionProductRepository,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->subscriptionProductRepository = $subscriptionProductRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this|AbstractBackend
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave($object)
    {
        $subscriptionProductRow = $this->subscriptionProductRepository
            ->getSubscriptionByProductId($object->getEntityId());
        $rowData = $subscriptionProductRow->getData();

        if ($this->moduleManager->isEnabled('Wagento_Subscription')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $subscriptionProductFactory = $objectManager->create('Wagento\Subscription\Api\Data\ProductInterfaceFactory')
                ->create();
            $subscriptionConfig = $object->getSubscriptionConfigurate();

            /*add subscription record in wagento_subscription_products*/
            if ($subscriptionConfig != 'no' && $subscriptionConfig != null) {
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
        } else {
            return $this;
        }
    }
}