<?php


namespace Dbm\CmsLayouts\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Responsive
 * @package Dbm\CmsLayouts\Block\Adminhtml\Config\Field
 */
class Responsive extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('size', ['label' => __('Screen size from'), 'renderer' => false, 'class' => 'required-entry validate-digits']);
        $this->addColumn('items', ['label' => __('Number of items'), 'renderer' => false, 'class' => 'required-entry validate-digits']);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
