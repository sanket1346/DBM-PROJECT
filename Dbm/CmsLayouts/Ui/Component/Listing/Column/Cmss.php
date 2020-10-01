<?php

namespace Dbm\CmsLayouts\Ui\Component\Listing\Column;

use Dbm\CmsLayouts\Helper\Data as cmsHelper;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Cmss
 * @package Dbm\CmsLayouts\Ui\Component\Listing\Column
 */
class Cmss extends Column
{
    /**
     * @var cmsHelper
     */
    protected $helperData;

    /**
     * Cmss constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param cmsHelper $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        cmsHelper $helperData,
        array $components = [],
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['layouts_id'])) {
                    $id = $item['layouts_id'];
                    $data = $this->helperData->getCmsCollection($id)->getSize();
                    $item[$this->getData('name')] = ($data > 0) ? $data . '<span> Home Block </span>' : '<b>' . __("No cms added") . '</b>';
                }
            }
        }

        return $dataSource;
    }
}
