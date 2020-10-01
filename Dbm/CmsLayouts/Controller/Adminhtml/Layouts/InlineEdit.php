<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Layouts;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Dbm\CmsLayouts\Model\Layouts;
use Dbm\CmsLayouts\Model\LayoutsFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Layouts
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Cms Factory
     *
     * @var LayoutsFactory
     */
    protected $layoutsFactory;

    /**
     * InlineEdit constructor.
     * @param JsonFactory $jsonFactory
     * @param LayoutsFactory $layoutsFactory
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        LayoutsFactory $layoutsFactory,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->layoutsFactory = $layoutsFactory;

        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!(!empty($postItems) && $this->getRequest()->getParam('isAjax'))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error'    => true,
            ]);
        }
        foreach (array_keys($postItems) as $layoutsId) {
            /** @var Layouts $layouts */
            $layouts = $this->layoutsFactory->create()->load($layoutsId);
            try {
                $layoutsData = $postItems[$layoutsId];
                $layouts->addData($layoutsData);
                $layouts->save();
            } catch (RuntimeException $e) {
                $messages[] = $this->getErrorWithLayoutsId($layouts, $e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithLayoutsId(
                    $layouts,
                    __('Something went wrong while saving the Cms.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => $error
        ]);
    }

    /**
     * Add layouts id to error message
     *
     * @param Layouts $layouts
     * @param $errorText
     *
     * @return string
     */
    protected function getErrorWithLayoutsId(Layouts $layouts, $errorText)
    {
        return '[Layouts ID: ' . $layouts->getId() . '] ' . $errorText;
    }
}
