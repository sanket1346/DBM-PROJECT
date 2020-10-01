<?php

namespace Dbm\CmsLayouts\Controller\Adminhtml\Cms;

use Dbm\CmsLayouts\Controller\Adminhtml\Cms;
use Dbm\CmsLayouts\Helper\Image;
use Dbm\CmsLayouts\Model\CmsFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use RuntimeException;

/**
 * Class Save
 * @package Dbm\CmsLayouts\Controller\Adminhtml\Cms
 */
class Save extends Cms
{
    /**
     * JS helper
     *
     * @var Js
     */
    public $jsHelper;
    /**
     * Image Helper
     *
     * @var Image
     */
    protected $imageHelper;

    /**
     * Save constructor.
     *
     * @param Image $imageHelper
     * @param CmsFactory $cmsFactory
     * @param Registry $registry
     * @param Js $jsHelper
     * @param Context $context
     */
    public function __construct(
        Image $imageHelper,
        CmsFactory $cmsFactory,
        Registry $registry,
        Js $jsHelper,
        Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        Json $serializer = null
    ) {
        $this->imageHelper = $imageHelper;
        $this->jsHelper = $jsHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $filesystem;
        parent::__construct($cmsFactory, $registry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getPost('cms')) {

            $data = $this->getRequest()->getParam('cms');
            $cms = $this->initCms();
            if (isset($data['store_ids'])) {
                $data['store_ids'] = implode(',', (array) $data['store_ids']);
            }

            //$this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_CMS, $cms->getImage());

            foreach ($_FILES as $key => $value) {

                $fileName = $value['name'];

                if ($fileName) {

                    try {

                        $uploader = $this->_fileUploaderFactory->create(['fileId' => $key]);
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(true);
                        $uploader->setAllowCreateFolders(true);
                        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('dbm/cmslayouts/cms/image');

                        $result = $uploader->save($mediaDirectory);

                        $cms->setData($key, $result['file']); //Database field name
                        $data[$key] = $result['file'];
                    } catch (\Exception $e) {
                        if ($e->getCode() == 0) {
                            $this->messageManager->addError($e->getMessage());
                        }
                    }
                } else {
                    if (isset($data[$key]['delete'])) {

                        $cms->setData(''); //Database field name
                        $data[$key] = '';
                    }
                }

            }

            $data['layoutss_ids'] = (isset($data['layoutss_ids']) && $data['layoutss_ids'])
            ? explode(',', $data['layoutss_ids']) : [];
            if ($this->getRequest()->getPost('layoutss', false)) {
                $cms->setTagsData(
                    $this->jsHelper->decodeGridSerializedInput($this->getRequest()->getPost('layoutss', false))
                );
            }

            foreach ($data as $k => $val) {
                if (strpos($k, 'image') !== false) {
                    if (isset($val['value'])) {
                        $data[$k] = $val['value'];
                    }

                }
            }

            $unsetfromdata = $data;
            unset($unsetfromdata['form_key']);
            $serializeData = $this->serializer->serialize($unsetfromdata);
            $data['cms_serialize_data'] = $serializeData;

            $cms->addData($data);

            $this->_eventManager->dispatch(
                'cmslayouts_cms_prepare_save',
                [
                    'cms' => $cms,
                    'request' => $this->getRequest(),
                ]
            );
            try {

                $cms->save();

                $this->messageManager->addSuccess(__('The Cms has been saved.'));
                $this->_session->setDbmCmsLayoutsCmsData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'cmslayouts/*/edit',
                        [
                            'cms_id' => $cms->getId(),
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Cms.'));
            }

            $this->_getSession()->setData('dbm_cmsLayouts_cms_data', $data);
            $resultRedirect->setPath(
                'cmslayouts/*/edit',
                [
                    'cms_id' => $cms->getId(),
                    '_current' => true,
                ]
            );

            return $resultRedirect;
        }

        $resultRedirect->setPath('cmslayouts/*/');

        return $resultRedirect;
    }
}
