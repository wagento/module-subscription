<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Adminhtml\Sales;

use Wagento\Subscription\Model\SubscriptionSalesRepository;
use Wagento\Subscription\Model\SubscriptionSalesFactory;

/**
 * Class Save
 * @package Wagento\Subscription\Controller\Adminhtml\Sales
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var SubscriptionSalesRepository
     */
    protected $subscriptionSalesRepository;

    /**
     * @var SubscriptionSalesFactory
     */
    protected $subSalesFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param SubscriptionSalesRepository $subscriptionSalesRepository
     * @param SubscriptionSalesFactory $subSalesFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        SubscriptionSalesRepository $subscriptionSalesRepository,
        SubscriptionSalesFactory $subSalesFactory
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->subscriptionSalesRepository = $subscriptionSalesRepository;
        $this->subSalesFactory = $subSalesFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|
     * \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();
        if ($isPost) {
            $saleSubModel = $this->subSalesFactory->create();
            $formData = $isPost['salesSub'];
            if ($formData['how_many'] == '') {
                $formData['how_many'] = null;
            }
            $id = $isPost['salesSub']['id'];
            if ($isPost['salesSub']['id']) {
                $saleSubModel->load($id);
            }
            $saleSubModel->setData($formData);
            try {
                $saleSubModel->save();
                $this->messageManager->addSuccessMessage(__('Subscription Profile #%1 Saved Successfully.', $id));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('subscription/sales/view', ['id' => $saleSubModel->getId(), '_current' => true]);
                    return;
                }
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
            $this->_redirect('subscription/sales/view');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wagento_Subscription::subscription_grid');
    }
}
