<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Dbm\CmsLayouts\Model\LayoutsFactory;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Controller\Adminhtml
 */
abstract class Layouts extends Action
{
    /**
     * Layouts Factory
     *
     * @var LayoutsFactory
     */
    protected $layoutsFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Layouts constructor.
     *
     * @param LayoutsFactory $layoutsFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        LayoutsFactory $layoutsFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->layoutsFactory = $layoutsFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Init Layouts
     *
     * @return \Dbm\CmsLayouts\Model\Layouts
     */
    protected function initLayouts()
    {
        $layoutsId = (int)$this->getRequest()->getParam('layouts_id');
        /** @var \Dbm\CmsLayouts\Model\Layouts $layouts */
        $layouts = $this->layoutsFactory->create();
        if ($layoutsId) {
            $layouts->load($layoutsId);
        }
        $this->coreRegistry->register('cmslayouts_layouts', $layouts);

        return $layouts;
    }
}
