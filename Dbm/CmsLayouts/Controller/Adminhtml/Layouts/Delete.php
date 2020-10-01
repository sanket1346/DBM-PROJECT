<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml\Layouts;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Dbm\CmsLayouts\Controller\Adminhtml\Layouts;
use Dbm\CmsLayouts\Model\Cms;

/**
 * Class Delete
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Layouts
 */
class Delete extends Layouts
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            /** @var Cms $cms */
            $this->layoutsFactory->create()
                ->load($this->getRequest()->getParam('layouts_id'))
                ->delete();
            $this->messageManager->addSuccess(__('The layouts has been deleted.'));
        } catch (Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            $resultRedirect->setPath(
                'cmslayouts/*/edit',
                ['layouts_id' => $this->getRequest()->getParam('layouts_id')]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('cmslayouts/*/');

        return $resultRedirect;
    }
}
