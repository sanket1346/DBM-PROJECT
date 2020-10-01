<?php

namespace Dbm\CmsLayouts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Location
 * @package Dbm\CmsLayouts\Model\Config\Source
 */
class Location implements ArrayInterface
{
    const ALLPAGE_CONTENT_TOP = 'allpage.content-top';
    const ALLPAGE_CONTENT_BOTTOM = 'allpage.content-bottom';
    const ALLPAGE_PAGE_TOP = 'allpage.page-top';
    const ALLPAGE_PAGE_BOTTOM = 'allpage.footer-container';
    const HOMEPAGE_CONTENT_TOP = 'cms_index_index.content-top';
    const HOMEPAGE_CONTENT_BOTTOM = 'cms_index_index.content-bottom';
    const HOMEPAGE_PAGE_TOP = 'cms_index_index.page-top';
    const HOMEPAGE_PAGE_BOTTOM = 'cms_index_index.footer-container';
    const CATEGORY_CONTENT_TOP = 'catalog_category_view.content-top';
    const CATEGORY_CONTENT_BOTTOM = 'catalog_category_view.content-bottom';
    const CATEGORY_PAGE_TOP = 'catalog_category_view.page-top';
    const CATEGORY_PAGE_BOTTOM = 'catalog_category_view.footer-container';
    const CATEGORY_SIDEBAR_TOP = 'catalog_category_view.sidebar-top';
    const CATEGORY_SIDEBAR_BOTTOM = 'catalog_category_view.sidebar-bottom';
    const PRODUCT_CONTENT_TOP = 'catalog_product_view.content-top';
    const PRODUCT_CONTENT_BOTTOM = 'catalog_product_view.content-bottom';
    const PRODUCT_PAGE_TOP = 'catalog_product_view.page-top';
    const PRODUCT_PAGE_BOTTOM = 'catalog_product_view.footer-container';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Home Page'),
                'value' => [
                    [
                        'label' => __('Top of content'),
                        'value' => self::HOMEPAGE_CONTENT_TOP,
                    ],
                ],
            ],
        ];

        return $options;
    }
}
