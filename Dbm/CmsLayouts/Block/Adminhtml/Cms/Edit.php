<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Cms;

use Dbm\CmsLayouts\Model\Cms;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Dbm\CmsLayouts\Block\Adminhtml\Cms
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
        parent::__construct($context, $data);

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Initialize Cms edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'cms_id';
        $this->_blockGroup = 'Dbm_CmsLayouts';
        $this->_controller = 'adminhtml_cms';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Home Block'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form',
                        ],
                    ],
                ],
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Home Block'));
    }

    /**
     * Retrieve text for header element depending on loaded Cms
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Cms $cms */
        $cms = $this->getCms();
        if ($cms->getId()) {
            return __("Edit Cms '%1'", $this->escapeHtml($cms->getName()));
        }

        return __('New Home Block');
    }

    /**
     * @return mixed
     */
    public function getCms()
    {
        return $this->coreRegistry->registry('cmslayouts_cms');
    }
}
