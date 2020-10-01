<?php

namespace Dbm\CmsLayouts\Helper;

use Dbm\CmsLayouts\Helper\AbstractData;
use Dbm\CmsLayouts\Model\CmsFactory;
use Dbm\CmsLayouts\Model\Config\Source\Effect;
use Dbm\CmsLayouts\Model\LayoutsFactory;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\Collection;
use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Dbm\CmsLayouts\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'cmslayouts';
    /**
     * @var CmsFactory
     */
    public $cmsFactory;
    /**
     * @var LayoutsFactory
     */
    public $layoutsFactory;
    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var HttpContext
     */
    protected $httpContext;

    protected $_storeConfig;

    protected $_storeManager;

    protected $categoryFactory;

    protected $_filesystem;
    protected $_imageFactory;

    /**
     * Data constructor.
     *
     * @param DateTime $date
     * @param Context $context
     * @param HttpContext $httpContext
     * @param CmsFactory $cmsFactory
     * @param LayoutsFactory $layoutsFactory
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        DateTime $date,
        Context $context,
        HttpContext $httpContext,
        CmsFactory $cmsFactory,
        LayoutsFactory $layoutsFactory,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $CategoryFactory
    ) {
        $this->date = $date;
        $this->httpContext = $httpContext;
        $this->cmsFactory = $cmsFactory;
        $this->layoutsFactory = $layoutsFactory;
        $this->_storeConfig = $scopeConfig;
        $this->categoryFactory = $CategoryFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    public function getPlaceholderImage()
    {
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $placeholderPath = $this->_storeConfig->getValue('catalog/placeholder/image_placeholder'); //Base Image
        $fullUrl = $mediaBaseUrl . 'catalog/product/placeholder/' . $placeholderPath;

        return $fullUrl;

    }

    public function getMediaUrl()
    {
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaBaseUrl;

    }

    public function getProductCollectionFromCategory($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        return $category->getProductCollection()->addAttributeToSelect('*');

    }

    /**
     * @param \Dbm\CmsLayouts\Model\Layouts $layouts
     *
     * @return false|string
     */
    public function getCmsOptions($layouts)
    {
        if ($layouts && $layouts->getDesign() === '1') {
            //not use Config
            $config = $layouts->getData();
        } else {
            $config = $this->getModuleConfig('cmslayouts_design');
        }

        $defaultOpt = $this->getDefaultConfig($config);
        $responsiveOpt = $this->getResponsiveConfig($layouts);
        $effectOpt = $this->getEffectConfig($layouts);

        $layoutsOptions = array_merge($defaultOpt, $responsiveOpt, $effectOpt);

        return self::jsonEncode($layoutsOptions);
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    public function getDefaultConfig($configs)
    {
        $basicConfig = [];
        foreach ($configs as $key => $value) {
            if (in_array(
                $key,
                ['autoWidth', 'autoHeight', 'loop', 'nav', 'dots', 'lazyLoad', 'autoplay', 'autoplayTimeout']
            )) {
                $basicConfig[$key] = (int) $value;
            }
        }

        return $basicConfig;
    }

    /**
     * @param null $layouts
     *
     * @return array
     */
    public function getResponsiveConfig($layouts = null)
    {
        $defaultResponsive = $this->getModuleConfig('cmslayouts_design/responsive');
        $layoutsResponsive = $layouts->getIsResponsive();

        if ((!$defaultResponsive && !$layoutsResponsive) || (!$layoutsResponsive && $layouts->getDesign())) {
            return ['items' => 1];
        }

        $responsiveItemsValue = $layouts->getDesign()
        ? $layouts->getResponsiveItems()
        : $this->getModuleConfig('cmslayouts_design/item_layouts');

        try {
            $responsiveItems = $this->unserialize($responsiveItemsValue);
        } catch (Exception $e) {
            $responsiveItems = [];
        }

        $result = [];
        foreach ($responsiveItems as $config) {
            $size = $config['size'] ?: 0;
            $items = $config['items'] ?: 0;
            $result[$size] = ['items' => $items];
        }

        return ['responsive' => $result];
    }

    /**
     * @param $layouts
     *
     * @return array
     */
    public function getEffectConfig($layouts)
    {
        if (!$layouts) {
            return [];
        }

        if ($layouts->getEffect() === Effect::LAYOUTS) {
            return [];
        }

        return ['animateOut' => $layouts->getEffect()];
    }

    /**
     * @param null $id
     *
     * @return Collection
     */
    public function getCmsCollection($id = null)
    {
        $collection = $this->cmsFactory->create()->getCollection();

        $collection->join(
            ['cms_layouts' => $collection->getTable('dbm_cmslayouts_cms_layouts')],
            'main_table.cms_id=cms_layouts.cms_id AND cms_layouts.layouts_id=' . $id,
            ['position']
        );

        $collection->addOrder('position', 'ASC');

        return $collection;
    }

    /**
     * @return \Dbm\CmsLayouts\Model\ResourceModel\Layouts\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveLayoutss()
    {
        /** @var \Dbm\CmsLayouts\Model\ResourceModel\Layouts\Collection $collection */
        $collection = $this->layoutsFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1);
        /*->addOrder('priority');*/

        $collection->getSelect()
            ->where('FIND_IN_SET(0, store_ids) OR FIND_IN_SET(?, store_ids)', $this->storeManager->getStore()->getId());

        return $collection;
    }
}
