<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Layouts;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab\Cms;
use Dbm\CmsLayouts\Controller\Adminhtml\Layouts;
use Dbm\CmsLayouts\Model\LayoutsFactory;

/**
 * Class Cmss
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Layouts
 */
class Cmss extends Layouts
{
    /**
     * Result layout factory
     *
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Cmss constructor.
     *
     * @param LayoutFactory $resultLayoutFactory
     * @param LayoutsFactory $cmsFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        LayoutFactory $resultLayoutFactory,
        LayoutsFactory $cmsFactory,
        Registry $registry,
        Context $context
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($cmsFactory, $registry, $context);
    }

    /**
     * @return Layout
     */
    public function execute()
    {
        $this->initLayouts();
        $resultLayout = $this->resultLayoutFactory->create();
        /** @var Cms $cmssBlock */
        $cmssBlock = $resultLayout->getLayout()->getBlock('layouts.edit.tab.cms');
        if ($cmssBlock) {
            $cmssBlock->setLayoutsCmss($this->getRequest()->getPost('layouts_cmss', null));
        }

        return $resultLayout;
    }
}
