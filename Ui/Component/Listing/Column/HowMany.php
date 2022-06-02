<?php
/**
 * Copyright Wagento Creative LLC ©, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wagento\Subscription\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Wagento\Subscription\Helper\Data;

class Howmany extends Column
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Howmany constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $helper,
        array $components = [],
        array $data = []
    ) {

        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['how_many']) && $item['how_many'] != 0) {
                    $frequencyUnit = $this->helper->getHowManyUnits($item['frequency']);
                    $item[$this->getData('name')] = $item['how_many'] . " " . $frequencyUnit;
                } else {
                    $item[$this->getData('name')] = __("N/A");
                }
            }
        }
        return $dataSource;
    }
}
