<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Layouts;

use Dbm\CmsLayouts\Controller\Adminhtml\Layouts;
use Dbm\CmsLayouts\Model\LayoutsFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use RuntimeException;
use Zend_Filter_Input;

/**
 * Class Save
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Layouts
 */
class Save extends Layouts
{
    /**
     * JS helper
     *
     * @var Js
     */
    protected $jsHelper;

    /**
     * Date filter
     *
     * @var Date
     */
    protected $_dateFilter;

    /**
     * Save constructor.
     *
     * @param Js $jsHelper
     * @param LayoutsFactory $layoutsFactory
     * @param Registry $registry
     * @param Context $context
     * @param Date $dateFilter
     */
    public function __construct(
        Js $jsHelper,
        LayoutsFactory $layoutsFactory,
        Registry $registry,
        Context $context,
        Date $dateFilter
    ) {
        $this->jsHelper = $jsHelper;
        $this->_dateFilter = $dateFilter;

        parent::__construct($layoutsFactory, $registry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getPost('layouts')) {
            $data = $this->_filterData($this->getRequest()->getPost('layouts'));

            $layouts = $this->initLayouts();

            if (isset($data['location'])) {
                $data['location'] = implode(',', (array) $data['location']);
            }

            $cmss = $this->getRequest()->getPost('cmss', -1);
            if ($cmss != -1) {
                $layouts->setCmssData($this->jsHelper->decodeGridSerializedInput($cmss));
            }
            $layouts->addData($data);

            $this->_eventManager->dispatch(
                'cmslayouts_layouts_prepare_save',
                [
                    'layouts' => $layouts,
                    'request' => $this->getRequest(),
                ]
            );

            try {
                $layouts->save();
                $this->messageManager->addSuccess(__('The Layouts has been saved.'));
                $this->_session->setDbmCmsLayoutsLayoutsData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'cmslayouts/*/edit',
                        [
                            'layouts_id' => $layouts->getId(),
                            '_current' => true,
                        ]
                    );

                    return $resultRedirect;
                }
                $resultRedirect->setPath('cmslayouts/*/');

                return $resultRedirect;
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Layouts.'));
            }

            $this->_getSession()->setDbmCmsLayoutsLayoutsData($data);
            $resultRedirect->setPath(
                'cmslayouts/*/edit',
                [
                    'layouts_id' => $layouts->getId(),
                    '_current' => true,
                ]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('cmslayouts/*/');

        return $resultRedirect;
    }

    /**
     * filter values
     *
     * @param array $data
     *
     * @return array
     */
    protected function _filterData($data)
    {
        $inputFilter = new Zend_Filter_Input(['from_date' => $this->_dateFilter], [], $data);
        $data = $inputFilter->getUnescaped();

        if (isset($data['responsive_items'])) {
            unset($data['responsive_items']['__empty']);
        }

        if ($this->getRequest()->getParam('cmss')) {
            $data['cms_ids'] = $this->getRequest()->getParam('cmss');
        }

        return $data;
    }
}
