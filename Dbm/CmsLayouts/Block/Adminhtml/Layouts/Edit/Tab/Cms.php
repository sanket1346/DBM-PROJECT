<?php

namespace Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab;

use Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\Status;
use Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render\Type;
use Dbm\CmsLayouts\Model\CmsFactory;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\Collection;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\CollectionFactory as CmsCollectionFactory;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data as backendHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Class Cms
 * @package Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab
 */
class Cms extends Extended implements TabInterface
{
    /**
     * Cms collection factory
     *
     * @var CmsCollectionFactory
     */
    protected $cmsCollectionFactory;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Cms factory
     *
     * @var CmsFactory
     */
    protected $cmsFactory;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * Cms constructor.
     *
     * @param CmsCollectionFactory $cmsCollectionFactory
     * @param Registry $coreRegistry
     * @param CmsFactory $cmsFactory
     * @param Context $context
     * @param backendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        CmsCollectionFactory $cmsCollectionFactory,
        Registry $coreRegistry,
        CmsFactory $cmsFactory,
        Context $context,
        backendHelper $backendHelper,
        Store $systemStore,
        array $data = []
    ) {
        $this->cmsCollectionFactory = $cmsCollectionFactory;
        $this->coreRegistry = $coreRegistry;
        $this->cmsFactory = $cmsFactory;
        $this->_systemStore = $systemStore;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('cms_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getLayouts()->getId()) {
            $this->setDefaultFilter(['in_cmss' => 1]);
        }
    }

    /**
     * @return Extended|void
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->cmsCollectionFactory->create();
        if ($this->getLayouts()->getId()) {
            $constraint = 'related.layouts_id=' . $this->getLayouts()->getId();
        } else {
            $constraint = 'related.layouts_id=0';
        }
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('dbm_cmslayouts_cms_layouts')],
            'related.cms_id=main_table.cms_id AND ' . $constraint,
            ['position']
        );
        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * @return $this|Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_cmss', [
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_cms',
            'values' => $this->_getSelectedCmss(),
            'align' => 'center',
            'index' => 'cms_id',
        ]);
        $this->addColumn('cms_id', [
            'header' => __('ID'),
            'sortable' => true,
            'index' => 'cms_id',
            'type' => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);

        $this->addColumn('store_ids', [
            'header' => __('Store Views'),
            'index' => 'store_ids',
            'type' => 'store',
            'store_all' => true,
            'store_view' => true,
            'renderer' => 'Dbm\CmsLayouts\Block\Adminhtml\Layouts\Edit\Tab\Renderer\Store',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'index' => 'name',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name',
        ]);

        $this->addColumn('type', [
            'header' => __('Type'),
            'index' => 'type',
            'header_css_class' => 'col-type',
            'column_css_class' => 'col-type',
            'renderer' => Type::class,
        ]);

        $this->addColumn('status', [
            'header' => __('Status'),
            'index' => 'status',
            'header_css_class' => 'col-status',
            'column_css_class' => 'col-status',
            'renderer' => Status::class,
        ]);

        $this->addColumn('position', [
            'header' => __('Position'),
            'name' => 'position',
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'position',
            'editable' => true,
        ]);

        return $this;
    }

    /**
     * Retrieve selected Cmss
     * @return array
     */
    protected function _getSelectedCmss()
    {
        $cmss = $this->getLayoutsCmss();
        if (!is_array($cmss)) {
            $cmss = $this->getLayouts()->getCmssPosition();

            return array_keys($cmss);
        }

        return $cmss;
    }

    /**
     * Retrieve selected Cmss
     * @return array
     */
    public function getSelectedCmss()
    {
        $selected = $this->getLayouts()->getCmssPosition();
        if (!is_array($selected)) {
            $selected = [];
        } else {
            foreach ($selected as $key => $value) {
                $selected[$key] = ['position' => $value];
            }
        }

        return $selected;
    }

    /**
     * @param \Dbm\CmsLayouts\Model\Cms|Object $item
     *
     * @return string
     */
    public function getRowUrl($item)
    {
        return '';
    }

    /**
     * get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/cmssGrid',
            [
                'layouts_id' => $this->getLayouts()->getId(),
            ]
        );
    }

    /**
     * @return \Dbm\CmsLayouts\Model\Layouts
     */
    public function getLayouts()
    {
        return $this->coreRegistry->registry('cmslayouts_layouts');
    }

    /**
     * @param Column $column
     *
     * @return $this|Extended
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() === 'in_cmss') {
            $cmsIds = $this->_getSelectedCmss();
            if (empty($cmsIds)) {
                $cmsIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.cms_id', ['in' => $cmsIds]);
            } else {
                if ($cmsIds) {
                    $this->getCollection()->addFieldToFilter('main_table.cms_id', ['nin' => $cmsIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Assign Home Block');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('cmslayouts/layouts/cmss', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
