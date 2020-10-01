<?php


namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;
use Dbm\CmsLayouts\Helper\Data;
use Dbm\CmsLayouts\Model\ResourceModel\Layouts\Collection;
use Dbm\CmsLayouts\Model\ResourceModel\Layouts\CollectionFactory as LayoutsCollectionFactory;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render
 */
class Layouts extends Multiselect
{
    /**
     * Authorization
     *
     * @var AuthorizationInterface
     */
    public $authorization;

    /**
     * @var LayoutsCollectionFactory
     */
    public $collectionFactory;

    /**
     * Layouts constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param LayoutsCollectionFactory $collectionFactory
     * @param AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        LayoutsCollectionFactory $collectionFactory,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization     = $authorization;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @inheritdoc
     */
    public function getElementHtml()
    {
        $html = '<div class="admin__field-control admin__control-grouped">';
        $html .= '<div id="cms-layouts-select" class="admin__field" data-bind="scope:\'layoutscms\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="cms[layouts_ids]" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= $this->getAfterElementHtml();

        return $html;
    }

    /**
     * Attach Blog Tag suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "cmslayouts": {
                                "component": "uiComponent",
                                "children": {
                                    "cms_select_layouts": {
                                        "component": "Magento_Catalog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . Data::jsonEncode($this->getLayoutsCollection()) . ',
                                            "value": ' . Data::jsonEncode($this->getValues()) . ',
                                            "config": {
                                                "dataScope": "cms_select_layouts",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';

        return $html;
    }

    /**
     * @return mixed
     */
    public function getLayoutsCollection()
    {
        /* @var $collection Collection */
        $collection = $this->collectionFactory->create();
        $layoutsById = [];
        foreach ($collection as $layouts) {
            $layoutsId = $layouts->getId();
            $layoutsById[$layoutsId]['value']     = $layoutsId;
            $layoutsById[$layoutsId]['is_active'] = 1;
            $layoutsById[$layoutsId]['label']     = $layouts->getName();
        }

        return $layoutsById;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $values = $this->getValue();

        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (empty($values)) {
            return [];
        }

        /* @var $collection Collection */
        $collection = $this->collectionFactory->create()->addIdFilter($values);

        $options = [];
        foreach ($collection as $layouts) {
            $options[] = $layouts->getId();
        }

        return $options;
    }
}
