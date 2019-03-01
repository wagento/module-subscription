<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Subscription\Block\Adminhtml\Sales\Subscription\View;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Wagento\Subscription\Model\SubscriptionSales;
use Wagento\Subscription\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SubscriptionDetails extends Generic implements TabInterface
{
    /**
     * @var SubscriptionSales
     */
    protected $subscriptionSales;

    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * SubscriptionDetails constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SubscriptionSales $subscriptionSales
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SubscriptionSales $subscriptionSales,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->subscriptionSales = $subscriptionSales;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('sales_subscription');

        $customerId = $model->getCustomerId();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('salesSub_');
        $form->setFieldNameSuffix('salesSub');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Subscription Information')]
        );

        $frequency = $model->getSubFrequency();

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
        $fieldset->addField(
            'how_many',
            'text',
            [
                'name' => 'how_many',
                'label' => __('Maximum Number of Subscription Cycle'),
                'required' => false,
                'after_element_html' =>$this->helper->getHowManyUnits($frequency)
            ]
        );

        $fieldset->addField(
            'next_renewed',
            'date',
            [
                'name' => 'next_renewed',
                'label' => __('Next Renewal Date'),
                'date_format' => 'dd-MM-yyyy',
                'required' => true
            ]
        );

        if($this->getIsRequiredShipping()) {
            $fieldset->addField(
                'shipping_address_id',
                'select',
                [
                    'name' => 'shipping_address_id',
                    'label' => __('Shipping Address'),
                    'required' => true,
                    'values' => $this->helper->getCustomerAddressInline($customerId)
                ]
            );
        }

        $fieldset->addField(
            'billing_address_id',
            'select',
            [
                'name' => 'billing_address_id',
                'label' => __('Billing Address'),
                'required' => true,
                'values' => $this->helper->getCustomerAddressInline($customerId)
            ]
        );

        $fieldset->addField(
            'public_hash',
            'select',
            [
                'name' => 'public_hash',
                'label' => __('Card Details'),
                'required' => true,
                'values' => $this->helper->getCardCollection($customerId)
            ]
        );

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Subscription Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Subscription Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsRequiredShipping()
    {
        $model = $this->_coreRegistry->registry('sales_subscription');
        $productId = $model->getSubProductId();
        $product = $this->productRepository->getById($productId);
        $productTypes = ['virtual', 'downloadable'];

        if ($product) {
            $productType = $product->getTypeId();
            if (!in_array($productType, $productTypes)) {
                return true;
            }
        }
        return false;
    }
}
