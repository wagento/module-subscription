<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Ajax;

class Unubscribecart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /** @var \Wagento\Subscription\Helper\Product */

    protected $subProductHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * Unubscribecart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     * @param \Magento\Catalog\Model\ProductRepository $product
     * @param \Magento\Checkout\Model\CartFactory $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Catalog\Model\ProductRepository $product,
        \Magento\Checkout\Model\CartFactory $cart
    ) {
    
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subProductHelper = $subProductHelper;
        $this->product = $product;
        $this->cart = $cart;
    }

    public function execute()
    {
        $itemJson = $this->helper->jsonDecode($this->getRequest()->getContent());
        $item = $this->helper->jsonDecode($itemJson);
        if ($item['product_id']) {
            $product_id = $item['product_id'];
            $items = $this->cart->create()->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                if ($product_id == $item->getProductId()) {
                    try {
                        $item->setIsSubscribed(0);
                        $item->save();
                        $productName = $this->product->getById($product_id)->getName();
                        $message = 'The Product ' . $productName . ' has been unsubscribed succesfully';
                        $response_json = [
                            'status' => 'success',
                            'message' => $message
                        ];
                        $this->messageManager->addSuccessMessage(__($message));
                    } catch (\Exception $e) {
                        $message = 'The product couldn\'t have been un subscribed due to ' . $e->getMessage();
                        ;
                        $response_json = [
                            'status' => 'warning',
                            'message' => $message
                        ];
                        $this->messageManager->addWarningMessage(__($message));
                    }
                    break;
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response_json);
    }
}
