<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml\Layouts;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Dbm\CmsLayouts\Controller\Adminhtml\Layouts;
use Dbm\CmsLayouts\Model\LayoutsFactory;

/**
 * Class Edit
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Layouts
 */
class Edit extends Layouts
{
    const ADMIN_RESOURCE = 'Dbm_CmsLayouts::layouts';

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
     * @param LayoutsFactory $layoutsFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        LayoutsFactory $layoutsFactory,
        Registry $registry,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($layoutsFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('layouts_id');
        /** @var \Dbm\CmsLayouts\Model\Layouts $layouts */
        $layouts = $this->initLayouts();

        if ($id) {
            $layouts->load($id);
            if (!$layouts->getId()) {
                $this->messageManager->addError(__('This Layouts no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'cmslayouts/*/edit',
                    [
                        'layouts_id' => $layouts->getId(),
                        '_current'  => true
                    ]
                );

                return $resultRedirect;
            }
        }

        $data = $this->_session->getData('cmslayouts_layouts_data', true);
        if (!empty($data)) {
            $layouts->setData($data);
        }

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dbm_CmsLayouts::layouts');
        $resultPage->getConfig()->getTitle()
            ->set(__('Layoutss'))
            ->prepend($layouts->getId() ? $layouts->getName() : __('New Layouts'));

        return $resultPage;
    }
}
