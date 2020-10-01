<?php


namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Dbm\CmsLayouts\Model\Cms;
use Dbm\CmsLayouts\Model\CmsFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
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
     * @var CmsFactory
     */
    protected $cmsFactory;

    /**
     * constructor
     *
     * @param JsonFactory $jsonFactory
     * @param CmsFactory $cmsFactory
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        CmsFactory $cmsFactory,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->cmsFactory = $cmsFactory;

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
        foreach (array_keys($postItems) as $cmsId) {
            /** @var Cms $cms */
            $cms = $this->cmsFactory->create()->load($cmsId);
            try {
                $cmsData = $postItems[$cmsId];//todo: handle dates
                $cms->addData($cmsData);
                $cms->save();
            } catch (RuntimeException $e) {
                $messages[] = $this->getErrorWithCmsId($cms, $e->getMessage());
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithCmsId(
                    $cms,
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
     * Add Cms id to error message
     *
     * @param Cms $cms
     * @param string $errorText
     *
     * @return string
     */
    protected function getErrorWithCmsId(Cms $cms, $errorText)
    {
        return '[Cms ID: ' . $cms->getId() . '] ' . $errorText;
    }
}
