<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wagento\Subscription\Block\Adminhtml\Sales\Order\Creditmemo;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice.
     *
     * @var null|\Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model.
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get credit memo.
     *
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     * Initialize payment fee totals.
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();
        if (!$this->getSource()->getInitialFee()) {
            return $this;
        }
        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'initialfee',
                'strong' => false,
                'value' => $this->getSource()->getInitialFee(),
                'label' => __('Subscription Initial Fee'),
            ]
        );

        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}
