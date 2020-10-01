<?php

namespace Dbm\CmsLayouts\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\Collection;
use Dbm\CmsLayouts\Model\ResourceModel\Cms\CollectionFactory;

/**
 * @method Layouts setName($name)
 * @method Layouts setDescription($description)
 * @method Layouts setStatus($status)
 * @method Layouts setConfigSerialized($configSerialized)
 * @method mixed getName()
 * @method mixed getDescription()
 * @method mixed getStatus()
 * @method mixed getConfigSerialized()
 * @method Layouts setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Layouts setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 * @method Layouts setCmssData(array $data)
 * @method array getCmssData()
 * @method Layouts setIsChangedCmsList(bool $flag)
 * @method bool getIsChangedCmsList()
 * @method Layouts setAffectedCmsIds(array $ids)
 * @method bool getAffectedCmsIds()
 */
class Layouts extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'dbm_cmslayouts_layouts';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'dbm_cmslayouts_layouts';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'dbm_cmslayouts_layouts';

    /**
     * Cms Collection
     *
     * @var Collection
     */
    protected $cmsCollection;

    /**
     * Cms Collection Factory
     *
     * @var CollectionFactory
     */
    protected $cmsCollectionFactory;

    /**
     * constructor
     *
     * @param CollectionFactory $cmsCollectionFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        CollectionFactory $cmsCollectionFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cmsCollectionFactory = $cmsCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dbm\CmsLayouts\Model\ResourceModel\Layouts');
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
        $values = [];
        $values['status'] = '1';

        return $values;
    }

    /**
     * @return array|mixed
     */
    public function getCmssPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('cmss_position');
        if ($array === null) {
            $array = $this->getResource()->getCmssPosition($this);
            $this->setData('cmss_position', $array);
        }

        return $array;
    }

    /**
     * @return Collection
     */
    public function getSelectedCmssCollection()
    {
        if ($this->cmsCollection === null) {
            $collection = $this->cmsCollectionFactory->create();
            $collection->getSelect()->join(
                ['cms_layouts' => $this->getResource()->getTable('dbm_cmslayouts_cms_layouts')],
                'main_table.cms_id=cms_layouts.cms_id AND cms_layouts.layouts_id=' . $this->getId(),
                ['position']
            );
            $this->cmsCollection = $collection;
        }

        return $this->cmsCollection;
    }
}
