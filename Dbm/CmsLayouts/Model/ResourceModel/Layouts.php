<?php


namespace Dbm\CmsLayouts\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Dbm\CmsLayouts\Helper\Data as cmsHelper;
use Zend_Serializer_Exception;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Model\ResourceModel
 */
class Layouts extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Cms relation model
     *
     * @var string
     */
    protected $layoutsCmsTable;

    /**
     * Event Manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var cmsHelper
     */
    protected $cmsHelper;

    /**
     * Layouts constructor.
     *
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param Context $context
     * @param cmsHelper $helperData
     */
    public function __construct(
        DateTime $date,
        ManagerInterface $eventManager,
        Context $context,
        cmsHelper $helperData
    ) {
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->cmsHelper = $helperData;

        parent::__construct($context);
        $this->layoutsCmsTable = $this->getTable('dbm_cmslayouts_cms_layouts');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dbm_cmslayouts_layouts', 'layouts_id');
    }

    /**
     * Retrieves Layouts Name from DB by passed id.
     *
     * @param $id
     *
     * @return string
     * @throws LocalizedException
     */
    public function getLayoutsNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('layouts_id = :layouts_id');
        $binds = ['layouts_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * before save callback
     *
     * @param AbstractModel $object
     *
     * @return AbstractDb
     * @throws Zend_Serializer_Exception
     */
    protected function _beforeSave(AbstractModel $object)
    {
        //set default Update At and Create At time post
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        $location = $object->getLocation();
        if (is_array($location)) {
            $object->setLocation(implode(',', $location));
        }

        $storeIds = $object->getStoreIds();
        if (is_array($storeIds)) {
            $object->setStoreIds(implode(',', $storeIds));
        }

        $groupIds = $object->getCustomerGroupIds();
        if (is_array($groupIds)) {
            $object->setCustomerGroupIds(implode(',', $groupIds));
        }

        $responsiveItems = $object->getResponsiveItems();
        if ($responsiveItems && is_array($responsiveItems)) {
            $object->setResponsiveItems($this->cmsHelper->serialize($responsiveItems));
        } else {
            $object->setResponsiveItems($this->cmsHelper->serialize([]));
        }

        return parent::_beforeSave($object);
    }

    /**
     * after save callback
     *
     * @param AbstractModel|\Dbm\CmsLayouts\Model\Layouts $object
     *
     * @return AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveCmsRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     * @throws Zend_Serializer_Exception
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        if ($object->getResponsiveItems() !== null) {
            $object->setResponsiveItems($this->cmsHelper->unserialize($object->getResponsiveItems()));
        } else {
            $object->setResponsiveItems(null);
        }

        return $this;
    }

    /**
     * @param \Dbm\CmsLayouts\Model\Layouts $layouts
     *
     * @return array
     */
    public function getCmssPosition(\Dbm\CmsLayouts\Model\Layouts $layouts)
    {
        $select = $this->getConnection()->select()->from(
            $this->layoutsCmsTable,
            ['cms_id', 'position']
        )
            ->where(
                'layouts_id = :layouts_id'
            );
        $bind = ['layouts_id' => (int)$layouts->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Dbm\CmsLayouts\Model\Layouts $layouts
     *
     * @return $this
     */
    protected function saveCmsRelation(\Dbm\CmsLayouts\Model\Layouts $layouts)
    {
        $layouts->setIsChangedCmsList(false);
        $id = $layouts->getId();
        $cmss = $layouts->getCmssData();
        if ($cmss === null) {
            return $this;
        }
        $oldCmss = $layouts->getCmssPosition();
        $insert = array_diff_key($cmss, $oldCmss);
        $delete = array_diff_key($oldCmss, $cmss);
        $update = array_intersect_key($cmss, $oldCmss);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldCmss[$key]) && $oldCmss[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['cms_id IN(?)' => array_keys($delete), 'layouts_id=?' => $id];
            $adapter->delete($this->layoutsCmsTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $cmsId => $position) {
                $data[] = [
                    'layouts_id' => (int)$id,
                    'cms_id' => (int)$cmsId,
                    'position'  => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->layoutsCmsTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $cmsId => $position) {
                $where = ['layouts_id = ?' => (int)$id, 'cms_id = ?' => (int)$cmsId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->layoutsCmsTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $cmsIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'dbm_cmslayouts_layouts_after_save_cmss',
                ['layouts' => $layouts, 'cms_ids' => $cmsIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $layouts->setIsChangedCmsList(true);
            $cmsIds = array_keys($insert + $delete + $update);
            $layouts->setAffectedCmsIds($cmsIds);
        }

        return $this;
    }
}
