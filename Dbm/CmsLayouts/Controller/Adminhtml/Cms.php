<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Dbm\CmsLayouts\Model\CmsFactory;

/**
 * Class Cms
 * @package Dbm\CmsLayouts\Controller\Adminhtml
 */
abstract class Cms extends Action
{
    /**
     * Cms Factory
     *
     * @var CmsFactory
     */
    protected $cmsFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var RedirectFactory
     */

    /**
     * constructor
     *
     * @param CmsFactory $cmsFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        CmsFactory $cmsFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->cmsFactory = $cmsFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Init Cms
     *
     * @return \Dbm\CmsLayouts\Model\Cms
     */
    protected function initCms()
    {
        $cmsId = (int)$this->getRequest()->getParam('cms_id');
        /** @var \Dbm\CmsLayouts\Model\Cms $cms */
        $cms = $this->cmsFactory->create();
        if ($cmsId) {
            $cms->load($cmsId);
        }
        $this->coreRegistry->register('cmslayouts_cms', $cms);

        return $cms;
    }
}
