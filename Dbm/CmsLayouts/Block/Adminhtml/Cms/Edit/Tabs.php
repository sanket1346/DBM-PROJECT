<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit;

/**
 * @method Tabs setTitle(string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cms_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Home Block Information'));
    }
}
