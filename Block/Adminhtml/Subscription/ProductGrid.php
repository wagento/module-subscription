<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Block\Adminhtml\Subscription;

use Wagento\Subscription\Model\ResourceModel\Product\CollectionFactory;

class ProductGrid extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'products/assign_products.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Wagento\Subscription\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * ProductGrid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Wagento\Subscription\Block\Adminhtml\Products\Edit\Tab\Product::class,
                'category.product.grid'
            );
        }

        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * Get product json function.
     *
     * @param null|mixed $id
     *
     * @return string
     */
    public function getProductsJson($id = null)
    {
        $subscriptionId = isset($id) ? $id : $this->getRequest()->getParam('id');

        $vProducts = $this->_productCollectionFactory->create()
            ->addFieldToFilter('subscription_id', $subscriptionId)
            ->addFieldToSelect('product_id')
        ;
        $products = [];
        foreach ($vProducts as $product) {
            $products[$product->getProductId()] = '';
        }

        if (!empty($products)) {
            return $this->jsonEncoder->encode($products);
        }

        return '{}';
    }

    /**
     * Get item function.
     *
     * @return mixed
     */
    public function getItem()
    {
        return $this->registry->registry('entity_id');
    }
}
