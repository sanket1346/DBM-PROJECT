<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Type
 * @package Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render
 */
class Type extends AbstractRenderer
{
    /**
     * Render cms type
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $type = $row->getData($this->getColumn()->getIndex());
        switch ($type) {
            case 1:
                $type = 'Full Width';
                break;
            case 2:
                $type = '2 Column';
                break;
            case 3:
                $type = '3 Column';
                break;
            case 4:
                $type = '4 Column';
                break;
            case 5:
                $type = 'Feature Product';
                break;
            case 6:
                $type = 'Top Seller Product';
                break;
        }

        return $type;
    }
}
