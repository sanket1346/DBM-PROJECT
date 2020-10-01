<?php

namespace Dbm\CmsLayouts\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Dbm\CmsLayouts\Model\Config\Source\Image as configImage;
use Dbm\CmsLayouts\Model\ResourceModel\Cms as ResourceCms;
use Dbm\CmsLayouts\Model\ResourceModel\Layouts\Collection;
use Dbm\CmsLayouts\Model\ResourceModel\Layouts\CollectionFactory as layoutsCollectionFactory;

/**
 * @method Cms setName($name)
 * @method Cms setUploadFile($uploadFile)
 * @method Cms setUrl($url)
 * @method Cms setType($type)
 * @method Cms setStatus($status)
 * @method mixed getName()
 * @method mixed getUploadFile()
 * @method mixed getUrl()
 * @method mixed getType()
 * @method mixed getStatus()
 * @method Cms setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Cms setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 * @method Cms setLayoutssData(array $data)
 * @method array getLayoutssData()
 * @method Cms setLayoutssIds(array $layoutsIds)
 * @method array getLayoutssIds()
 * @method Cms setIsChangedLayoutsList(bool $flag)
 * @method bool getIsChangedLayoutsList()
 * @method Cms setAffectedLayoutsIds(array $ids)
 * @method bool getAffectedLayoutsIds()
 */
class Cms extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'dbm_cmslayouts_cms';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'dbm_cmslayouts_cms';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'dbm_cmslayouts_cms';

    /**
     * Layouts Collection
     *
     * @var Collection
     */
    protected $layoutsCollection;

    /**
     * Layouts Collection Factory
     *
     * @var layoutsCollectionFactory
     */
    protected $layoutsCollectionFactory;

    /**
     * @var configImage
     */
    protected $imageModel;

    /**
     * Cms constructor.
     *
     * @param layoutsCollectionFactory $layoutsCollectionFactory
     * @param Context $context
     * @param Registry $registry
     * @param configImage $configImage
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        layoutsCollectionFactory $layoutsCollectionFactory,
        Context $context,
        Registry $registry,
        configImage $configImage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->layoutsCollectionFactory = $layoutsCollectionFactory;
        $this->imageModel = $configImage;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceCms::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return ['status => 1', 'type' => '0'];
    }

    /**
     * @return ResourceModel\Layouts\Collection
     */
    public function getSelectedLayoutssCollection()
    {
        if ($this->layoutsCollection === null) {
            /** @var \Dbm\CmsLayouts\Model\ResourceModel\Layouts\Collection $collection */
            $collection = $this->layoutsCollectionFactory->create();
            $collection->getSelect()->join(
                ['cms_layouts' => $this->getResource()->getTable('dbm_cmslayouts_cms_layouts')],
                'main_table.layouts_id=cms_layouts.layouts_id AND cms_layouts.cms_id=' . $this->getId(),
                ['position']
            );
            $collection->addFieldToFilter('status', 1);

            $this->layoutsCollection = $collection;
        }

        return $this->layoutsCollection;
    }

    /**
     * get full image url
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageModel->getBaseUrl() . $this->getImage();
    }

    /**
     * @return array
     */
    public function getLayoutsIds()
    {
        if (!$this->hasData('layouts_ids')) {
            $ids = $this->getResource()->getLayoutsIds($this);

            $this->setData('layouts_ids', $ids);
        }

        return (array) $this->getData('layouts_ids');
    }
}
