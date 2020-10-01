<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab;

use Dbm\CmsLayouts\Model\Config\Source\Location;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab
 */
class Layouts extends Generic implements TabInterface
{
    /**
     * Status options
     *
     * @var Enabledisable
     */
    protected $statusOptions;

    /**
     * @var Location
     */
    protected $_location;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    protected $_objectConverter;

    /**
     * Layouts constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Enabledisable $statusOptions
     * @param Location $location
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Enabledisable $statusOptions,
        Location $location,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter,
        Store $systemStore,
        array $data = []
    ) {
        $this->statusOptions = $statusOptions;
        $this->_location = $location;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_systemStore = $systemStore;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Dbm\CmsLayouts\Model\Layouts $layouts */
        $layouts = $this->_coreRegistry->registry('cmslayouts_layouts');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('layouts_');
        $form->setFieldNameSuffix('layouts');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Homepage Layouts Information'),
            'class' => 'fieldset-wide',
        ]);
        if ($layouts->getId()) {
            $fieldset->addField('layouts_id', 'hidden', ['name' => 'layouts_id']);
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
            'values' => array_merge(['' => ''], $this->statusOptions->toOptionArray()),
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
            if (!$layouts->hasData('store_ids')) {
                $layouts->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name' => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId(),
            ]);
        }

        $fieldset->addField('location', 'multiselect', [
            'name' => 'location',
            'label' => __('Position'),
            'title' => __('Position'),
            'values' => $this->_location->toOptionArray(),
            'note' => __('Select the position to display block.'),
        ]);

        $form->addValues($layouts->getData());
        $this->setForm($form);

        return parent::_prepareForm();
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
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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
}
