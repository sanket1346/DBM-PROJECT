<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Dbm\CmsLayouts\Controller\Adminhtml\Cms;
use Dbm\CmsLayouts\Model\CmsFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
 */
class Edit extends Cms
{
    const ADMIN_RESOURCE = 'Dbm_CmsLayouts::cms';

    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param PageFactory $resultPageFactory
     * @param CmsFactory $cmsFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        CmsFactory $cmsFactory,
        Registry $registry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($cmsFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('cms_id');
        /** @var \Dbm\CmsLayouts\Model\Cms $cms */
        $cms = $this->initCms();

        if ($id) {
            $cms->load($id);
            if (!$cms->getId()) {
                $this->messageManager->addError(__('This Cms no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'cmslayouts/*/edit',
                    [
                        'cms_id' => $cms->getId(),
                        '_current' => true,
                    ]
                );

                return $resultRedirect;
            }
        }

        $data = $this->_session->getData('cmslayouts_cms_data', true);
        if (!empty($data)) {
            $cms->setData($data);
        }

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dbm_CmsLayouts::cms');
        $resultPage->getConfig()->getTitle()
            ->set(__('Cmss'))
            ->prepend($cms->getId() ? $cms->getName() : __('New Home Block'));

        return $resultPage;
    }
}
