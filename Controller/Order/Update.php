<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Wagento\Subscription\Model\SubscriptionSalesRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException;

/**
 * Class Update
 * @package Wagento\Subscription\Controller\Order
 */
class Update extends Action
{
    /**
     * @var SubscriptionSalesRepository
     */
    protected $subSalesRepository;

    /**
     * Update constructor.
     * @param Context $context
     * @param SubscriptionSalesRepository $subSalesRepository
     */
    public function __construct(
        Context $context,
        SubscriptionSalesRepository $subSalesRepository
    ) {
    
        parent::__construct($context);
        $this->subSalesRepository = $subSalesRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $subSales = $this->subSalesRepository->getById($data['order_id']);
            $subSales->setBillingAddressId($data['subscription-billing']);
            if (isset($data['subscription-shipping'])) {
                $subSales->setShippingAddressId($data['subscription-shipping']);
            }
            $subSales->setPublicHash($data['subscription-card']);
            $id = $subSales->save()->getId();
            $this->messageManager->addSuccessMessage(__('Subscription Profile #%1 Updated Successfully.', $id));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        } catch (CouldNotSaveException $ex) {
            $this->messageManager->addErrorMessage(__($ex->getMessage()));
        }
        return $resultRedirect->setPath('subscription/order/view', ['order_id' => $data['order_id']]);
    }
}
