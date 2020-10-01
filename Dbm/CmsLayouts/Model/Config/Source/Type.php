<?php

namespace Dbm\CmsLayouts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Dbm\CmsLayouts\Model\Config\Source
 */
class Type implements ArrayInterface
{
    const IMAGE = '0';
    const CONTENT = '1';
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('Please Select'),
            ],
            [
                'value' => 1,
                'label' => __('Full Width'),
            ],
            [
                'value' => 2,
                'label' => __('2 Column'),
            ],
            [
                'value' => 3,
                'label' => __('3 Column'),
            ],
            [
                'value' => 4,
                'label' => __('4 Column'),
            ],
            [
                'value' => 5,
                'label' => __('Feature Product'),
            ],
            [
                'value' => 6,
                'label' => __('Top Seller Product'),
            ],

        ];

        return $options;
    }
}
