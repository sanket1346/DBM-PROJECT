<?php


namespace Dbm\CmsLayouts\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Block\Adminhtml
 */
class Layouts extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_layouts';
        $this->_blockGroup = 'Dbm_CmsLayouts';
        $this->_headerText = __('Layoutss');
        $this->_addButtonLabel = __('Create New Layouts');

        parent::_construct();
    }
}
