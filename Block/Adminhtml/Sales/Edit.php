<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Sales;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Wagento\Subscription\Model\SubscriptionSales;
use Wagento\Subscription\Model\SubscriptionSalesRepository;

class Edit extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var SubscriptionSalesRepository
     */
    protected $subSalesRepository;

    /**
     * @var SubscriptionSales
     */
    protected $subSalesmodel;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SubscriptionSalesRepository $subSalesRepository
     * @param SubscriptionSales $subscriptionSales
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SubscriptionSalesRepository $subSalesRepository,
        SubscriptionSales $subscriptionSales,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->subSalesRepository = $subSalesRepository;
        $this->subSalesmodel = $subscriptionSales;
        parent::__construct($context, $data);
    }

    /**
     * Construct function.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_sales_subscription';
        $this->_blockGroup = 'Wagento_Subscription';
        $id = $this->getRequest()->getParam('id');
        $salesSubData = $this->subSalesRepository->getById($id);
        $currentStatus = $salesSubData->getStatus();
        $this->_coreRegistry->register('sales_subscription', $salesSubData);
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Subscription'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');

        $this->buttonList->add(
            'reset',
            [
                'label' => __('Reset'),
                'onclick' => 'setLocation(\''.$this->getResetUrl().'\')',
                'class' => 'reset',
            ],
            0
        );

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                        ],
                    ],
                ],
            ],
            -100
        );

        if (0 != $currentStatus) {
            if (3 != $currentStatus) {
                $this->buttonList->add(
                    'cancel',
                    [
                        'label' => __('Cancel Subscription'),
                        'onclick' => 'setLocation(\''.$this->getCancelUrl().'\')',
                        'class' => 'add',
                    ],
                    0
                );
            }
        }

        if (1 == $currentStatus || 0 == $currentStatus) {
            $this->buttonList->add(
                'pause',
                [
                    'label' => __('Pause Subscription'),
                    'onclick' => 'setLocation(\''.$this->getPauseUrl().'\')',
                    'class' => 'add',
                ],
                0
            );
        }

        if (2 == $currentStatus || 0 == $currentStatus) {
            $this->buttonList->add(
                'activate',
                [
                    'label' => __('Activate Subscription'),
                    'onclick' => 'setLocation(\''.$this->getActivateUrl().'\')',
                    'class' => 'add',
                ],
                0
            );
        }
    }

    /**
     * Prepare layout.
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";

        return parent::_prepareLayout();
    }

    /**
     * Get pause url function.
     *
     * @return string
     */
    protected function getPauseUrl()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->getUrl('subscription/sales/pause', ['id' => $id]);
    }

    /**
     * Get cancel url function.
     *
     * @return string
     */
    protected function getCancelUrl()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->getUrl('subscription/sales/cancel', ['id' => $id]);
    }

    /**
     * Get active url function.
     *
     * @return string
     */
    protected function getActivateUrl()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->getUrl('subscription/sales/activate', ['id' => $id]);
    }

    /**
     * Get reset url function.
     *
     * @return string
     */
    protected function getResetUrl()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->getUrl(
            'subscription/sales/view',
            [
                'id' => $id,
                'active_tab' => 'subscription_edit',
            ]
        );
    }
}
