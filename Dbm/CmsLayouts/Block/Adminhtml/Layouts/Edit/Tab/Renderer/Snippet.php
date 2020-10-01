<?php


namespace Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Snippet
 * @package Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab\Renderer
 */
class Snippet extends AbstractElement
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $layoutsId = '1';
        $html = '<ul class="cms-location-display"><li><span>';
        $html .= __('Add Widget with name "Cms Layouts widget" and set "Layouts Id" for it.');
        $html .= '</span></li><li><span>';
        $html .= __('CMS Page/Static Block');
        $html .= '</span><code>{{block class="Dbm\CmsLayouts\Block\Widget" layouts_id="' . $layoutsId . '"}}</code><p>';
        $html .= __('You can paste the above block of snippet into any page in Magento 2 and set LayoutsId for it.');
        $html .= '</p></li><li><span>';
        $html .= __('Template .phtml file');
        $html .= '</span><code>' . $this->_escaper->escapeHtml('<?= $block->getLayout()->createBlock("Dbm\CmsLayouts\Block\Widget::class")->setLayoutsId(' . $layoutsId . ')->toHtml();?>') . '</code><p>';
        $html .= __('Open a .phtml file and insert where you want to display Cms Layouts.');
        $html .= '</p></li></ul>';

        return $html;
    }
}
