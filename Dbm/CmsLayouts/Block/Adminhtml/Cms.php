<?php

namespace Dbm\CmsLayouts\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Cms
 * @package Dbm\CmsLayouts\Block\Adminhtml
 */
class Cms extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_cms';
        $this->_blockGroup = 'Dbm_CmsLayouts';
        $this->_headerText = __('Cmss');
        $this->_addButtonLabel = __('Create New Home Block');

        parent::_construct();
    }
}
