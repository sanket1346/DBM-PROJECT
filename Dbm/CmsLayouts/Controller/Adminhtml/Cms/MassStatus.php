<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\CollectionFactory;

/**
 * Class MassStatus
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
 */
class MassStatus extends Action
{
    /**
     * Mass Action Filter
     *
     * @var Filter
     */
    public $filter;

    /**
     * Collection Factory
     *
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * MassStatus constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = (int)$this->getRequest()->getParam('status');
        $cmsUpdated = 0;
        foreach ($collection as $cms) {
            try {
                $cms->setStatus($status)
                    ->save();

                $cmsUpdated++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while updating status for %1.', $cms->getName()));
            }
        }

        if ($cmsUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $cmsUpdated));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
