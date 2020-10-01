<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab;

use Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\Image as CmsImage;
use Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\Layouts;
use Dbm\CmsLayouts\Helper\Data;
use Dbm\CmsLayouts\Helper\Image as HelperImage;
use Dbm\CmsLayouts\Model\Config\Source\Categorylist;
use Dbm\CmsLayouts\Model\Config\Source\Template;
use Dbm\CmsLayouts\Model\Config\Source\Type;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\System\Store;

/**
 * Class Cms
 * @package Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab
 */
class Cms extends Generic implements TabInterface
{
    /**
     * Type options
     *
     * @var Type
     */
    protected $typeOptions;

    /**
     * Category options
     *
     * @var Category
     */
    protected $categoryOptions;

    /**
     * Template options
     *
     * @var Template
     */
    protected $template;

    /**
     * Status options
     *
     * @var Enabledisable
     */
    protected $statusOptions;

    /**
     * @var HelperImage
     */
    protected $imageHelper;

    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var DataObject
     */
    protected $_objectConverter;

    /**
     * @var WysiwygConfig
     */
    protected $_wysiwygConfig;

    /**
     * Cms constructor.
     *
     * @param Type $typeOptions
     * @param Template $template
     * @param Enabledisable $statusOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param HelperImage $imageHelper
     * @param FieldFactory $fieldFactory
     * @param DataObject $objectConverter
     * @param WysiwygConfig $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Type $typeOptions,
        Categorylist $categoryOptions,
        Template $template,
        Enabledisable $statusOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HelperImage $imageHelper,
        FieldFactory $fieldFactory,
        DataObject $objectConverter,
        WysiwygConfig $wysiwygConfig,
        Store $systemStore,
        Json $serializer = null,
        array $data = []
    ) {
        $this->typeOptions = $typeOptions;
        $this->categoryOptions = $categoryOptions;
        $this->template = $template;
        $this->statusOptions = $statusOptions;
        $this->imageHelper = $imageHelper;
        $this->_fieldFactory = $fieldFactory;
        $this->_objectConverter = $objectConverter;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Dbm\CmsLayouts\Model\Cms $cms */
        $cms = $this->_coreRegistry->registry('cmslayouts_cms');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('cms_');
        $form->setFieldNameSuffix('cms');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Home Block Information'),
            'class' => 'fieldset-wide',
        ]);

        if ($cms->getId()) {
            $fieldset->addField(
                'cms_id',
                'hidden',
                ['name' => 'cms_id']
            );
        }

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => $this->statusOptions->toOptionArray(),
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock(Element::class);
            $fieldset->addField('store_ids', 'multiselect', [
                'name' => 'store_ids',
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'required' => true,
                'values' => $this->_systemStore->getStoreValuesForForm(false, true),
            ])->setRenderer($rendererBlock);
            if (!$cms->hasData('store_ids')) {
                $cms->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name' => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId(),
            ]);
        }

        $mainTypeField = $fieldset->addField('type', 'select', [
            'name' => 'type',
            'label' => __('Type'),
            'title' => __('Type'),
            'values' => $this->typeOptions->toOptionArray(),
            'required' => true,
        ]);

        // $fieldset->addField(
        //     'fullwidth',
        //     'note',
        //     [
        //         'label' => __('Full Width Block Image Priview'),
        //         'path' => 'http://192.168.1.136/magento235p1/pub/media/dbm/cmslayouts/cms/image/h/o/home_four_2.jpg',
        //     ]
        // );
        //

        $type1_previewimage = $fieldset->addField('type1_previewimage', 'Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\PreviewImage', [
            'name' => 'type1_previewimage',
            'label' => __('Preview Image'),
            'title' => __('Preview Image'),
            //'renderer' => 'Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\PreviewImage',
            //'path' => 'http://192.168.1.136/magento235p1/pub/media/dbm/cmslayouts/cms/image/h/o/home_four_2.jpg',
        ]);

        // Full Width

        $type1_image = $fieldset->addField('image', CmsImage::class, [
            'name' => 'image',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type1_url = $fieldset->addField(
            'type1_url',
            'text',
            [
                'name' => 'type1_url',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type1_block_back_color = $fieldset->addField('type1_block_back_color', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type1_block_back_color',
        ));

        $type1_title = $fieldset->addField('type1_title', 'text', [
            'name' => 'type1_title',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type1_small_title = $fieldset->addField('type1_small_title', 'text', [
            'name' => 'type1_small_title',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type1_button_text = $fieldset->addField('type1_button_text', 'text', [
            'name' => 'type1_button_text',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type1_button_back_color = $fieldset->addField('type1_button_back_color', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type1_button_back_color',
        ));

        $type1_button_text_color = $fieldset->addField('type1_button_text_color', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type1_button_text_color',
        ));

        $type1_content = $fieldset->addField(
            'type1_content',
            'editor',
            [
                'name' => 'type1_content',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type1_content_position = $fieldset->addField(
            "type1_content_position",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type1_content_position",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // 2column

        $type2_main_back_color = $fieldset->addField('type2_main_back_color', 'text', array(
            'label' => __('Button Main Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_main_back_color',
        ));

        $type2_image1 = $fieldset->addField('type2_image1', CmsImage::class, [
            'name' => 'type2_image1',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type2_url1 = $fieldset->addField(
            'type2_url1',
            'text',
            [
                'name' => 'type2_url1',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type2_block_back_color1 = $fieldset->addField('type2_block_back_color1', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_block_back_color1',
        ));

        $type2_title1 = $fieldset->addField('type2_title1', 'text', [
            'name' => 'type2_title1',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);

        $type2_small_title1 = $fieldset->addField('type2_small_title1', 'text', [
            'name' => 'type2_small_title1',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type2_button_text1 = $fieldset->addField('type2_button_text1', 'text', [
            'name' => 'type2_button_text1',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type2_button_back_color1 = $fieldset->addField('type2_button_back_color1', 'text', array(
            'label' => __('Button Background Color Column 1'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_button_back_color1',
        ));

        $type2_button_text_color1 = $fieldset->addField('type2_button_text_color1', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_button_text_color1',
        ));

        $type2_content1 = $fieldset->addField(
            'type2_content1',
            'editor',
            [
                'name' => 'type2_content1',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type2_content_position1 = $fieldset->addField(
            "type2_content_position1",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type2_content_position1",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        $fieldset->addField('note', 'note', array(
            'class' => 'border_css',
        ));

        $type2_image2 = $fieldset->addField('type2_image2', CmsImage::class, [
            'name' => 'type2_image2',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type2_url2 = $fieldset->addField(
            'type2_url2',
            'text',
            [
                'name' => 'type2_url2',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type2_block_back_color2 = $fieldset->addField('type2_block_back_color2', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_block_back_color2',
        ));

        $type2_title2 = $fieldset->addField('type2_title2', 'text', [
            'name' => 'type2_title2',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type2_small_title2 = $fieldset->addField('type2_small_title2', 'text', [
            'name' => 'type2_small_title2',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type2_button_text2 = $fieldset->addField('type2_button_text2', 'text', [
            'name' => 'type2_button_text2',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type2_button_back_color2 = $fieldset->addField('type2_button_back_color2', 'text', array(
            'label' => __('Button Background Color Column 2'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_button_back_color2',
        ));

        $type2_button_text_color2 = $fieldset->addField('type2_button_text_color2', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type2_button_text_color2',
        ));

        $type2_content2 = $fieldset->addField(
            'type2_content2',
            'editor',
            [
                'name' => 'type2_content2',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type2_content_position2 = $fieldset->addField(
            "type2_content_position2",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type2_content_position2",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // 3 Column

        $type3_main_back_color = $fieldset->addField('type3_main_back_color', 'text', array(
            'label' => __('Button Main Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_main_back_color',
        ));

        $type3_image1 = $fieldset->addField('type3_image1', CmsImage::class, [
            'name' => 'type3_image1',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type3_url1 = $fieldset->addField(
            'type3_url1',
            'text',
            [
                'name' => 'type3_url1',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type3_block_back_color1 = $fieldset->addField('type3_block_back_color1', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_block_back_color1',
        ));

        $type3_title1 = $fieldset->addField('type3_title1', 'text', [
            'name' => 'type3_title1',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);

        $type3_small_title1 = $fieldset->addField('type3_small_title1', 'text', [
            'name' => 'type3_small_title1',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type3_button_text1 = $fieldset->addField('type3_button_text1', 'text', [
            'name' => 'type3_button_text1',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type3_button_back_color1 = $fieldset->addField('type3_button_back_color1', 'text', array(
            'label' => __('Button Background Color Column 1'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_back_color1',
        ));

        $type3_button_text_color1 = $fieldset->addField('type3_button_text_color1', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_text_color1',
        ));

        $type3_content1 = $fieldset->addField(
            'type3_content1',
            'editor',
            [
                'name' => 'type3_content1',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type3_content_position1 = $fieldset->addField(
            "type3_content_position1",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type3_content_position1",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // $fieldset->addField('note31', 'note', array(
        //     'class' => 'border_css',
        // ));

        $type3_image2 = $fieldset->addField('type3_image2', CmsImage::class, [
            'name' => 'type3_image2',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type3_url2 = $fieldset->addField(
            'type3_url2',
            'text',
            [
                'name' => 'type3_url2',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type3_block_back_color2 = $fieldset->addField('type3_block_back_color2', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_block_back_color2',
        ));

        $type3_title2 = $fieldset->addField('type3_title2', 'text', [
            'name' => 'type3_title2',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type3_small_title2 = $fieldset->addField('type3_small_title2', 'text', [
            'name' => 'type3_small_title2',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type3_button_text2 = $fieldset->addField('type3_button_text2', 'text', [
            'name' => 'type3_button_text2',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type3_button_back_color2 = $fieldset->addField('type3_button_back_color2', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_back_color2',
        ));

        $type3_button_text_color2 = $fieldset->addField('type3_button_text_color2', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_text_color2',
        ));

        $type3_content2 = $fieldset->addField(
            'type3_content2',
            'editor',
            [
                'name' => 'type3_content2',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type3_content_position2 = $fieldset->addField(
            "type3_content_position2",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type3_content_position2",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // $fieldset->addField('note32', 'note', array(
        //     'class' => 'border_css',
        // ));

        $type3_image3 = $fieldset->addField('type3_image3', CmsImage::class, [
            'name' => 'type3_image3',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type3_url3 = $fieldset->addField(
            'type3_url3',
            'text',
            [
                'name' => 'type3_url3',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type3_block_back_color3 = $fieldset->addField('type3_block_back_color3', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_block_back_color3',
        ));

        $type3_title3 = $fieldset->addField('type3_title3', 'text', [
            'name' => 'type3_title3',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type3_small_title3 = $fieldset->addField('type3_small_title3', 'text', [
            'name' => 'type3_small_title3',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type3_button_text3 = $fieldset->addField('type3_button_text3', 'text', [
            'name' => 'type3_button_text3',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type3_button_back_color3 = $fieldset->addField('type3_button_back_color3', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_back_color3',
        ));

        $type3_button_text_color3 = $fieldset->addField('type3_button_text_color3', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type3_button_text_color3',
        ));

        $type3_content3 = $fieldset->addField(
            'type3_content3',
            'editor',
            [
                'name' => 'type3_content3',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type3_content_position3 = $fieldset->addField(
            "type3_content_position3",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type3_content_position3",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // 4 column
        $type4_main_back_color = $fieldset->addField('type4_main_back_color', 'text', array(
            'label' => __('Button Main Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_main_back_color',
        ));

        $type4_image1 = $fieldset->addField('type4_image1', CmsImage::class, [
            'name' => 'type4_image1',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type4_url1 = $fieldset->addField(
            'type4_url1',
            'text',
            [
                'name' => 'type4_url1',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type4_block_back_color1 = $fieldset->addField('type4_block_back_color1', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_block_back_color1',
        ));

        $type4_title1 = $fieldset->addField('type4_title1', 'text', [
            'name' => 'type4_title1',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);

        $type4_small_title1 = $fieldset->addField('type4_small_title1', 'text', [
            'name' => 'type4_small_title1',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type4_button_text1 = $fieldset->addField('type4_button_text1', 'text', [
            'name' => 'type4_button_text1',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type4_button_back_color1 = $fieldset->addField('type4_button_back_color1', 'text', array(
            'label' => __('Button Background Color Column 1'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_back_color1',
        ));

        $type4_button_text_color1 = $fieldset->addField('type4_button_text_color1', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_text_color1',
        ));

        $type4_content1 = $fieldset->addField(
            'type4_content1',
            'editor',
            [
                'name' => 'type4_content1',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type4_content_position1 = $fieldset->addField(
            "type4_content_position1",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type4_content_position1",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // $fieldset->addField('note41', 'note', array(
        //     'class' => 'border_css',
        // ));

        $type4_image2 = $fieldset->addField('type4_image2', CmsImage::class, [
            'name' => 'type4_image2',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type4_url2 = $fieldset->addField(
            'type4_url2',
            'text',
            [
                'name' => 'type4_url2',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type4_block_back_color2 = $fieldset->addField('type4_block_back_color2', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_block_back_color2',
        ));

        $type4_title2 = $fieldset->addField('type4_title2', 'text', [
            'name' => 'type4_title2',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type4_small_title2 = $fieldset->addField('type4_small_title2', 'text', [
            'name' => 'type4_small_title2',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type4_button_text2 = $fieldset->addField('type4_button_text2', 'text', [
            'name' => 'type4_button_text2',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type4_button_back_color2 = $fieldset->addField('type4_button_back_color2', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_back_color2',
        ));

        $type4_button_text_color2 = $fieldset->addField('type4_button_text_color2', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_text_color2',
        ));

        $type4_content2 = $fieldset->addField(
            'type4_content2',
            'editor',
            [
                'name' => 'type4_content2',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type4_content_position2 = $fieldset->addField(
            "type4_content_position2",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type4_content_position2",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        // // $fieldset->addField('note42', 'note', array(
        // //     'class' => 'border_css',
        // // ));

        $type4_image3 = $fieldset->addField('type4_image3', CmsImage::class, [
            'name' => 'type4_image3',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type4_url3 = $fieldset->addField(
            'type4_url3',
            'text',
            [
                'name' => 'type4_url3',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type4_block_back_color3 = $fieldset->addField('type4_block_back_color3', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_block_back_color3',
        ));

        $type4_title3 = $fieldset->addField('type4_title3', 'text', [
            'name' => 'type4_title3',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type4_small_title3 = $fieldset->addField('type4_small_title3', 'text', [
            'name' => 'type4_small_title3',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type4_button_text3 = $fieldset->addField('type4_button_text3', 'text', [
            'name' => 'type4_button_text3',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type4_button_back_color3 = $fieldset->addField('type4_button_back_color3', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_back_color3',
        ));

        $type4_button_text_color3 = $fieldset->addField('type4_button_text_color3', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_text_color3',
        ));

        $type4_content3 = $fieldset->addField(
            'type4_content3',
            'editor',
            [
                'name' => 'type4_content3',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type4_content_position3 = $fieldset->addField(
            "type4_content_position3",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type4_content_position3",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        $type4_image4 = $fieldset->addField('type4_image4', CmsImage::class, [
            'name' => 'type4_image4',
            'label' => __('Upload Image'),
            'title' => __('Upload Image'),
            'path' => $this->imageHelper->getBaseMediaPath(HelperImage::TEMPLATE_MEDIA_TYPE_CMS),
        ]);

        $type4_url4 = $fieldset->addField(
            'type4_url4',
            'text',
            [
                'name' => 'type4_url4',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => false,
            ]
        );

        $type4_block_back_color4 = $fieldset->addField('type4_block_back_color4', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_block_back_color4',
        ));

        $type4_title4 = $fieldset->addField('type4_title4', 'text', [
            'name' => 'type4_title4',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);
        $type4_small_title4 = $fieldset->addField('type4_small_title4', 'text', [
            'name' => 'type4_small_title4',
            'label' => __('Small Title'),
            'title' => __('Small Title'),
            'required' => false,
        ]);
        $type4_button_text4 = $fieldset->addField('type4_button_text4', 'text', [
            'name' => 'type4_button_text4',
            'label' => __('Button Text'),
            'title' => __('Button Text'),
            'required' => false,
        ]);

        $type4_button_back_color4 = $fieldset->addField('type4_button_back_color4', 'text', array(
            'label' => __('Button Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_back_color4',
        ));

        $type4_button_text_color4 = $fieldset->addField('type4_button_text_color4', 'text', array(
            'label' => __('Button Text Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type4_button_text_color4',
        ));

        $type4_content4 = $fieldset->addField(
            'type4_content4',
            'editor',
            [
                'name' => 'type4_content4',
                'label' => __('Content'),
                'wysiwyg' => true,
                'config' => $this->_wysiwygConfig->getConfig(),
                'required' => false,
            ]
        );

        $type4_content_position4 = $fieldset->addField(
            "type4_content_position4",
            "select",
            [
                "label" => __("Content position to display content"),
                "name" => "type4_content_position4",
                "values" => [
                    ["value" => 'left', "label" => __("Left")],
                    ["value" => 'right', "label" => __("Right")],
                    ["value" => 'center', "label" => __("Center")],
                ],

            ]
        );

        //Feature Product
        $type5_title1 = $fieldset->addField('type5_title1', 'text', [
            'name' => 'type5_title1',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);

        $type5_button_back_color1 = $fieldset->addField('type5_button_back_color1', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type5_button_back_color1',
        ));

        $type5_category1 = $fieldset->addField('type5_category1', 'select', [
            'name' => 'type5_category1',
            'label' => __('Category'),
            'title' => __('Category'),
            'values' => $this->categoryOptions->toOptionArray(),
            'required' => true,
        ]);

        $type5_limit1 = $fieldset->addField('type5_limit1', 'text', [
            'name' => 'type5_limit1',
            'label' => __('Limit'),
            'title' => __('Limit'),
            'required' => false,
        ]);

        //Top Seller Product
        $type6_title1 = $fieldset->addField('type6_title1', 'text', [
            'name' => 'type6_title1',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => false,
        ]);

        $type6_button_back_color1 = $fieldset->addField('type6_button_back_color1', 'text', array(
            'label' => __('Block Background Color'),
            'class' => 'jscolor {hash:true,refine:false}',
            'required' => false,
            'name' => 'type6_button_back_color1',
        ));

        $type6_category1 = $fieldset->addField('type6_category1', 'select', [
            'name' => 'type6_category1',
            'label' => __('Category'),
            'title' => __('Category'),
            'values' => $this->categoryOptions->toOptionArray(),
            'required' => true,
        ]);

        $type6_limit1 = $fieldset->addField('type6_limit1', 'text', [
            'name' => 'type6_limit1',
            'label' => __('Limit'),
            'title' => __('Limit'),
            'required' => false,
        ]);

        $fieldset->addField('layoutss_ids', Layouts::class, [
            'name' => 'layoutss_ids',
            'title' => __('Layouts'),
        ]);

        $mainTypeField->setAfterElementHtml('<script>
        //<![CDATA[
        require(["jquery","custom"], function ($,custom) {
        $(document).ready(function() {

            custom.showDefult();

            $("#cms_type").change(function () {
                custom.showDefult();
            });

            });
        });

        //]]>
        </script>');

        if (!$cms->getLayoutssIds()) {
            $cms->setLayoutssIds($cms->getLayoutsIds());
        }

        $cmsData = $this->_session->getData('cmslayouts_cms_data', true);
        if ($cmsData) {
            $cms->addData($cmsData);
        } else {
            if (!$cms->getId()) {
                $cms->addData($cms->getDefaultValues());
            }
        }

        $dependencies = $this->getLayout()->createBlock(Dependence::class)
            ->addFieldMap($mainTypeField->getHtmlId(), $mainTypeField->getName())
            ->addFieldMap($type1_image->getHtmlId(), $type1_image->getName())
            ->addFieldMap($type1_title->getHtmlId(), $type1_title->getName())
            ->addFieldMap($type1_small_title->getHtmlId(), $type1_small_title->getName())
            ->addFieldMap($type1_button_text->getHtmlId(), $type1_button_text->getName())
            ->addFieldMap($type1_button_back_color->getHtmlId(), $type1_button_back_color->getName())
            ->addFieldMap($type1_button_text_color->getHtmlId(), $type1_button_text_color->getName())
            ->addFieldMap($type1_content->getHtmlId(), $type1_content->getName())
            ->addFieldMap($type1_url->getHtmlId(), $type1_url->getName())
            ->addFieldMap($type1_content_position->getHtmlId(), $type1_content_position->getName())
            ->addFieldMap($type1_block_back_color->getHtmlId(), $type1_block_back_color->getName())
            ->addFieldMap($type1_previewimage->getHtmlId(), $type1_previewimage->getName())

            ->addFieldMap($type2_main_back_color->getHtmlId(), $type2_main_back_color->getName())
            ->addFieldMap($type2_image1->getHtmlId(), $type2_image1->getName())
            ->addFieldMap($type2_title1->getHtmlId(), $type2_title1->getName())
            ->addFieldMap($type2_small_title1->getHtmlId(), $type2_small_title1->getName())
            ->addFieldMap($type2_button_text1->getHtmlId(), $type2_button_text1->getName())
            ->addFieldMap($type2_button_back_color1->getHtmlId(), $type2_button_back_color1->getName())
            ->addFieldMap($type2_button_text_color1->getHtmlId(), $type2_button_text_color1->getName())
            ->addFieldMap($type2_url1->getHtmlId(), $type2_url1->getName())
            ->addFieldMap($type2_block_back_color1->getHtmlId(), $type2_block_back_color1->getName())
            ->addFieldMap($type2_content1->getHtmlId(), $type2_content1->getName())
            ->addFieldMap($type2_content_position1->getHtmlId(), $type2_content_position1->getName())

            ->addFieldMap($type2_image2->getHtmlId(), $type2_image2->getName())
            ->addFieldMap($type2_title2->getHtmlId(), $type2_title2->getName())
            ->addFieldMap($type2_small_title2->getHtmlId(), $type2_small_title2->getName())
            ->addFieldMap($type2_button_text2->getHtmlId(), $type2_button_text2->getName())
            ->addFieldMap($type2_button_back_color2->getHtmlId(), $type2_button_back_color2->getName())
            ->addFieldMap($type2_button_text_color2->getHtmlId(), $type2_button_text_color2->getName())
            ->addFieldMap($type2_url2->getHtmlId(), $type2_url2->getName())
            ->addFieldMap($type2_block_back_color2->getHtmlId(), $type2_block_back_color2->getName())
            ->addFieldMap($type2_content2->getHtmlId(), $type2_content2->getName())
            ->addFieldMap($type2_content_position2->getHtmlId(), $type2_content_position2->getName())

            ->addFieldMap($type3_main_back_color->getHtmlId(), $type3_main_back_color->getName())
            ->addFieldMap($type3_image1->getHtmlId(), $type3_image1->getName())
            ->addFieldMap($type3_title1->getHtmlId(), $type3_title1->getName())
            ->addFieldMap($type3_small_title1->getHtmlId(), $type3_small_title1->getName())
            ->addFieldMap($type3_button_text1->getHtmlId(), $type3_button_text1->getName())
            ->addFieldMap($type3_button_back_color1->getHtmlId(), $type3_button_back_color1->getName())
            ->addFieldMap($type3_button_text_color1->getHtmlId(), $type3_button_text_color1->getName())
            ->addFieldMap($type3_url1->getHtmlId(), $type3_url1->getName())
            ->addFieldMap($type3_block_back_color1->getHtmlId(), $type3_block_back_color1->getName())
            ->addFieldMap($type3_content1->getHtmlId(), $type3_content1->getName())
            ->addFieldMap($type3_content_position1->getHtmlId(), $type3_content_position1->getName())

            ->addFieldMap($type3_image2->getHtmlId(), $type3_image2->getName())
            ->addFieldMap($type3_title2->getHtmlId(), $type3_title2->getName())
            ->addFieldMap($type3_small_title2->getHtmlId(), $type3_small_title2->getName())
            ->addFieldMap($type3_button_text2->getHtmlId(), $type3_button_text2->getName())
            ->addFieldMap($type3_button_back_color2->getHtmlId(), $type3_button_back_color2->getName())
            ->addFieldMap($type3_button_text_color2->getHtmlId(), $type3_button_text_color2->getName())
            ->addFieldMap($type3_url2->getHtmlId(), $type3_url2->getName())
            ->addFieldMap($type3_block_back_color2->getHtmlId(), $type3_block_back_color2->getName())
            ->addFieldMap($type3_content2->getHtmlId(), $type3_content2->getName())
            ->addFieldMap($type3_content_position2->getHtmlId(), $type3_content_position2->getName())

            ->addFieldMap($type3_image3->getHtmlId(), $type3_image3->getName())
            ->addFieldMap($type3_title3->getHtmlId(), $type3_title3->getName())
            ->addFieldMap($type3_small_title3->getHtmlId(), $type3_small_title3->getName())
            ->addFieldMap($type3_button_text3->getHtmlId(), $type3_button_text3->getName())
            ->addFieldMap($type3_button_back_color3->getHtmlId(), $type3_button_back_color3->getName())
            ->addFieldMap($type3_button_text_color3->getHtmlId(), $type3_button_text_color3->getName())
            ->addFieldMap($type3_url3->getHtmlId(), $type3_url3->getName())
            ->addFieldMap($type3_block_back_color3->getHtmlId(), $type3_block_back_color3->getName())
            ->addFieldMap($type3_content3->getHtmlId(), $type3_content3->getName())
            ->addFieldMap($type3_content_position3->getHtmlId(), $type3_content_position3->getName())

            ->addFieldMap($type4_main_back_color->getHtmlId(), $type4_main_back_color->getName())
            ->addFieldMap($type4_image1->getHtmlId(), $type4_image1->getName())
            ->addFieldMap($type4_title1->getHtmlId(), $type4_title1->getName())
            ->addFieldMap($type4_small_title1->getHtmlId(), $type4_small_title1->getName())
            ->addFieldMap($type4_button_text1->getHtmlId(), $type4_button_text1->getName())
            ->addFieldMap($type4_button_back_color1->getHtmlId(), $type4_button_back_color1->getName())
            ->addFieldMap($type4_button_text_color1->getHtmlId(), $type4_button_text_color1->getName())
            ->addFieldMap($type4_url1->getHtmlId(), $type4_url1->getName())
            ->addFieldMap($type4_block_back_color1->getHtmlId(), $type4_block_back_color1->getName())
            ->addFieldMap($type4_content1->getHtmlId(), $type4_content1->getName())
            ->addFieldMap($type4_content_position1->getHtmlId(), $type4_content_position1->getName())

            ->addFieldMap($type4_image2->getHtmlId(), $type4_image2->getName())
            ->addFieldMap($type4_title2->getHtmlId(), $type4_title2->getName())
            ->addFieldMap($type4_small_title2->getHtmlId(), $type4_small_title2->getName())
            ->addFieldMap($type4_button_text2->getHtmlId(), $type4_button_text2->getName())
            ->addFieldMap($type4_button_back_color2->getHtmlId(), $type4_button_back_color2->getName())
            ->addFieldMap($type4_button_text_color2->getHtmlId(), $type4_button_text_color2->getName())
            ->addFieldMap($type4_url2->getHtmlId(), $type4_url2->getName())
            ->addFieldMap($type4_block_back_color2->getHtmlId(), $type4_block_back_color2->getName())
            ->addFieldMap($type4_content2->getHtmlId(), $type4_content2->getName())
            ->addFieldMap($type4_content_position2->getHtmlId(), $type4_content_position2->getName())

            ->addFieldMap($type4_image3->getHtmlId(), $type4_image3->getName())
            ->addFieldMap($type4_title3->getHtmlId(), $type4_title3->getName())
            ->addFieldMap($type4_small_title3->getHtmlId(), $type4_small_title3->getName())
            ->addFieldMap($type4_button_text3->getHtmlId(), $type4_button_text3->getName())
            ->addFieldMap($type4_button_back_color3->getHtmlId(), $type4_button_back_color3->getName())
            ->addFieldMap($type4_button_text_color3->getHtmlId(), $type4_button_text_color3->getName())
            ->addFieldMap($type4_url3->getHtmlId(), $type4_url3->getName())
            ->addFieldMap($type4_block_back_color3->getHtmlId(), $type4_block_back_color3->getName())
            ->addFieldMap($type4_content3->getHtmlId(), $type4_content3->getName())
            ->addFieldMap($type4_content_position3->getHtmlId(), $type4_content_position3->getName())

            ->addFieldMap($type4_image4->getHtmlId(), $type4_image4->getName())
            ->addFieldMap($type4_title4->getHtmlId(), $type4_title4->getName())
            ->addFieldMap($type4_small_title4->getHtmlId(), $type4_small_title4->getName())
            ->addFieldMap($type4_button_text4->getHtmlId(), $type4_button_text4->getName())
            ->addFieldMap($type4_button_back_color4->getHtmlId(), $type4_button_back_color4->getName())
            ->addFieldMap($type4_button_text_color4->getHtmlId(), $type4_button_text_color4->getName())
            ->addFieldMap($type4_url4->getHtmlId(), $type4_url4->getName())
            ->addFieldMap($type4_block_back_color4->getHtmlId(), $type4_block_back_color4->getName())
            ->addFieldMap($type4_content4->getHtmlId(), $type4_content4->getName())
            ->addFieldMap($type4_content_position4->getHtmlId(), $type4_content_position4->getName())

            ->addFieldMap($type5_title1->getHtmlId(), $type5_title1->getName())
            ->addFieldMap($type5_button_back_color1->getHtmlId(), $type5_button_back_color1->getName())
            ->addFieldMap($type5_category1->getHtmlId(), $type5_category1->getName())
            ->addFieldMap($type5_limit1->getHtmlId(), $type5_limit1->getName())

            ->addFieldMap($type6_title1->getHtmlId(), $type6_title1->getName())
            ->addFieldMap($type6_button_back_color1->getHtmlId(), $type6_button_back_color1->getName())
            ->addFieldMap($type6_category1->getHtmlId(), $type6_category1->getName())
            ->addFieldMap($type6_limit1->getHtmlId(), $type6_limit1->getName())

            ->addFieldDependence($type1_image->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_title->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_small_title->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_button_text->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_button_back_color->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_button_text_color->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_content->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_url->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_content_position->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_block_back_color->getName(), $mainTypeField->getName(), '1')
            ->addFieldDependence($type1_previewimage->getName(), $mainTypeField->getName(), '1')

            ->addFieldDependence($type1_previewimage->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_main_back_color->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_image1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_title1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_small_title1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_text1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_back_color1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_text_color1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_url1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_block_back_color1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_content1->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_content_position1->getName(), $mainTypeField->getName(), '2')

            ->addFieldDependence($type2_image2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_title2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_small_title2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_text2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_back_color2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_button_text_color2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_url2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_block_back_color2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_content2->getName(), $mainTypeField->getName(), '2')
            ->addFieldDependence($type2_content_position2->getName(), $mainTypeField->getName(), '2')

            ->addFieldDependence($type3_main_back_color->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_image1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_title1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_small_title1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_back_color1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text_color1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_url1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_block_back_color1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content1->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content_position1->getName(), $mainTypeField->getName(), '3')

            ->addFieldDependence($type3_image2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_title2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_small_title2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_back_color2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text_color2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_url2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_block_back_color2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content2->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content_position2->getName(), $mainTypeField->getName(), '3')

            ->addFieldDependence($type3_image3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_title3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_small_title3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_back_color3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_button_text_color3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_url3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_block_back_color3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content3->getName(), $mainTypeField->getName(), '3')
            ->addFieldDependence($type3_content_position3->getName(), $mainTypeField->getName(), '3')

            ->addFieldDependence($type4_main_back_color->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_image1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_title1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_small_title1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_back_color1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text_color1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_url1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_block_back_color1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content1->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content_position1->getName(), $mainTypeField->getName(), '4')

            ->addFieldDependence($type1_previewimage->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_image2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_title2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_small_title2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_back_color2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text_color2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_url2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_block_back_color2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content2->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content_position2->getName(), $mainTypeField->getName(), '4')

            ->addFieldDependence($type4_image3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_title3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_small_title3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_back_color3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text_color3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_url3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_block_back_color3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content3->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content_position3->getName(), $mainTypeField->getName(), '4')

            ->addFieldDependence($type4_image4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_title4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_small_title4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_back_color4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_button_text_color4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_url4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_block_back_color4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content4->getName(), $mainTypeField->getName(), '4')
            ->addFieldDependence($type4_content_position4->getName(), $mainTypeField->getName(), '4')

            ->addFieldDependence($type1_previewimage->getName(), $mainTypeField->getName(), '5')
            ->addFieldDependence($type5_title1->getName(), $mainTypeField->getName(), '5')
            ->addFieldDependence($type5_button_back_color1->getName(), $mainTypeField->getName(), '5')
            ->addFieldDependence($type5_category1->getName(), $mainTypeField->getName(), '5')
            ->addFieldDependence($type5_limit1->getName(), $mainTypeField->getName(), '5')

            ->addFieldDependence($type1_previewimage->getName(), $mainTypeField->getName(), '6')
            ->addFieldDependence($type6_title1->getName(), $mainTypeField->getName(), '6')
            ->addFieldDependence($type6_button_back_color1->getName(), $mainTypeField->getName(), '6')
            ->addFieldDependence($type6_category1->getName(), $mainTypeField->getName(), '6')
            ->addFieldDependence($type6_limit1->getName(), $mainTypeField->getName(), '6');

        // define field dependencies
        $this->setChild('form_after', $dependencies);

        $serializearry = $cms->getData();
        $set_data_arr = '';
        if (isset($serializearry['cms_serialize_data'])) {
            $set_data_arr = $this->serializer->unserialize($serializearry['cms_serialize_data']);
        }
        if (isset($serializearry['layout_id'])) {
            $set_data_arr['layout_id'] = $serializearry['layout_id'];
        }
        $form->setValues($set_data_arr);
        $form->addValues($cms->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
