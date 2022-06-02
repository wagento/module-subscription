<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;

class Unsubscribecart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var \Wagento\Subscription\Helper\Product
     */
    private $subProductHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $sidebar;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Unubscribe constructor.
     * @param Context $context
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Wagento\Subscription\Helper\Product $subProductHelper
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Magento\Quote\Model\Quote $quote
     * @param UrlInterface $url
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Wagento\Subscription\Helper\Product $subProductHelper,
        \Magento\Checkout\Model\CartFactory $cart,
        \Magento\Quote\Model\Quote $quote,
        UrlInterface $url,
        \Magento\Checkout\Model\Sidebar $sidebar,
        \Psr\Log\LoggerInterface $logger
    ) {

        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subProductHelper = $subProductHelper;
        $this->cart = $cart;
        $this->quote = $quote;
        $this->url = $url;
        $this->sidebar = $sidebar;
        $this->cart = $cart;
        $this->logger = $logger;
    }

    /**
     * Unsubscribecart execute function
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getContent();
        if (!empty($data)) {
            $itemJson = $this->helper->jsonDecode($data);
            $item = $this->helper->jsonDecode($itemJson);
            $productUnsubscribe = $item['product_id'];
            if ($productUnsubscribe) {
                try {
                    $items = $this->cart->create()->getQuote()->getAllVisibleItems();
                    foreach ($items as $key => $item) {
                        if ($productUnsubscribe == $item->getProductId() &&
                            $item->getIsSubscribed() == '1') {
                            $this->cart->create()->removeItem($item->getItemId())->save();
                            $this->sidebar->checkQuoteItem($item->getItemId());
                            $this->sidebar->removeQuoteItem($item->getItemId());
                        } else {
                            continue;
                        }
                    }
                    $message = __('Product %1 Unsubscibed Successfully', $item['product_name']);
                    $response_json = [
                        'status' => 'success',
                        'message' => $message
                    ];
                    $this->messageManager->addSuccessMessage(__($message));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                    $this->logger->critical($e);

                    $response_json = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        } else {
            $message = __('The Product have already been un-subscribed');
            $response_json = [
                'status' => 'warning',
                'message' => $message
            ];
            $this->messageManager->addWarningMessage(__($message));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response_json);
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return \Magento\Framework\App\Response\Http
     */
    protected function jsonResponse($error = '')
    {
        $response = $this->sidebar->getResponseData($error);
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
