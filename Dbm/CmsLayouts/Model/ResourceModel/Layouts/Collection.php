<?php

namespace Dbm\CmsLayouts\Model\ResourceModel\Layouts;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Zend_Db_Select;

/**
 * Class Collection
 * @package Dbm\CmsLayouts\Model\ResourceModel\Layouts
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'layouts_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'dbm_cmslayouts_layouts_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'layouts_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dbm\CmsLayouts\Model\Layouts', 'Dbm\CmsLayouts\Model\ResourceModel\Layouts');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);

        return $countSelect;
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     *
     * @return array
     */
    protected function _toOptionArray($valueField = 'layouts_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * add if filter
     *
     * @param $layoutsIds
     *
     * @return $this
     */
    public function addIdFilter($layoutsIds)
    {
        $condition = '';

        if (is_array($layoutsIds)) {
            if (!empty($layoutsIds)) {
                $condition = ['in' => $layoutsIds];
            }
        } elseif (is_numeric($layoutsIds)) {
            $condition = $layoutsIds;
        } elseif (is_string($layoutsIds)) {
            $ids = explode(',', $layoutsIds);
            if (empty($ids)) {
                $condition = $layoutsIds;
            } else {
                $condition = ['in' => $ids];
            }
        }

        if ($condition !== '') {
            $this->addFieldToFilter('layouts_id', $condition);
        }

        return $this;
    }

    /**
     * @param $customerGroup
     * @param $storeId
     *
     * @return $this
     */
    public function addActiveFilter($customerGroup = null, $storeId = null)
    {
        $this->addFieldToFilter('status', true);

        /* if (isset($customerGroup)) {
        $this->getSelect()
        ->where('FIND_IN_SET(0, customer_group_ids) OR FIND_IN_SET(?, customer_group_ids)', $customerGroup);
        }*/

        if (isset($storeId)) {
            $this->getSelect()
                ->where('FIND_IN_SET(0, store_ids) OR FIND_IN_SET(?, store_ids)', $storeId);
        }

        return $this;
    }
}
