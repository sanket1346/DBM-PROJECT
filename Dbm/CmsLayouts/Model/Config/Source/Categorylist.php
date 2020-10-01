<?php

namespace Dbm\CmsLayouts\Model\Config\Source;

use Magento\Catalog\Helper\Category;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Dbm\CmsLayouts\Model\Config\Source
 */
class Categorylist implements ArrayInterface
{
    protected $_categories;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collection)
    {
        $this->_categories = $collection;
    }

    public function toOptionArray()
    {

        $collection = $this->_categories->create();
        $collection->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);
        $itemArray = array('value' => '', 'label' => '--Please Select--');
        $options = [];
        $options = $itemArray;
        foreach ($collection as $category) {
            $options[] = ['value' => $category->getId(), 'label' => $category->getName()];
        }
        return $options;
    }
}
