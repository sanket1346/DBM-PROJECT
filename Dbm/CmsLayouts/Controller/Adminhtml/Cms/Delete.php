<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Dbm\CmsLayouts\Controller\Adminhtml\Cms;

/**
 * Class Delete
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
 */
class Delete extends Cms
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->cmsFactory->create()
                ->load($this->getRequest()->getParam('cms_id'))
                ->delete();
            $this->messageManager->addSuccess(__('The Cms has been deleted.'));
        } catch (Exception $e) {
            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());
            // go back to edit form
            $resultRedirect->setPath(
                'cmslayouts/*/edit',
                ['cms_id' => $this->getRequest()->getParam('cms_id')]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('cmslayouts/*/');

        return $resultRedirect;
    }
}
