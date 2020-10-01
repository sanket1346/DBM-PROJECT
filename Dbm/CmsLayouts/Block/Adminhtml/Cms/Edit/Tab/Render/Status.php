<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Status
 * @package Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render
 */
class Status extends AbstractRenderer
{
    /**
     * Render Cms status
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $status = $row->getData($this->getColumn()->getIndex());

        return $status === '1' ? 'Enable' : 'Disable';
    }
}
