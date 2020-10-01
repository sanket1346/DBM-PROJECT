<?php


namespace Dbm\CmsLayouts\Block\Adminhtml\Layouts;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Dbm\CmsLayouts\Model\Layouts;

/**
 * Class Edit
 * @package Dbm\CmsLayouts\Block\Adminhtml\Layouts
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Layouts edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'layouts_id';
        $this->_blockGroup = 'Dbm_CmsLayouts';
        $this->_controller = 'adminhtml_layouts';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Layouts'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Layouts'));
    }

    /**
     * Retrieve text for header element depending on loaded Layouts
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Layouts $layouts */
        $layouts = $this->getLayouts();
        if ($layouts->getId()) {
            return __("Edit Layouts '%1'", $this->escapeHtml($layouts->getName()));
        }

        return __('New Layouts');
    }

    /**
     * @return mixed
     */
    public function getLayouts()
    {
        return $this->coreRegistry->registry('cmslayouts_layouts');
    }
}
