<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class NewAction
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
 */
class NewAction extends Action
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
