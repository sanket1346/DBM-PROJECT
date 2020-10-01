<?php


namespace Dbm\CmsLayouts\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Cms
 * @package Dbm\CmsLayouts\Model\ResourceModel
 */
class Cms extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Layouts relation model
     *
     * @var string
     */
    protected $cmsLayoutsTable;

    /**
     * Event Manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * constructor
     *
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param Context $context
     */
    public function __construct(
        DateTime $date,
        ManagerInterface $eventManager,
        Context $context
    ) {
        $this->date = $date;
        $this->eventManager = $eventManager;

        parent::__construct($context);
        $this->cmsLayoutsTable = $this->getTable('dbm_cmslayouts_cms_layouts');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dbm_cmslayouts_cms', 'cms_id');
    }

    /**
     * @param $id
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCmsNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('cms_id = :cms_id');
        $binds = ['cms_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        //set default Update At and Create At time post
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveLayoutsRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param \Dbm\CmsLayouts\Model\Cms $cms
     *
     * @return $this
     */
    protected function saveLayoutsRelation(\Dbm\CmsLayouts\Model\Cms $cms)
    {
        $cms->setIsChangedLayoutsList(false);
        $id = $cms->getId();
        $layoutss = $cms->getLayoutssIds();
        if ($layoutss === null) {
            return $this;
        }
        $oldLayoutss = $cms->getLayoutsIds();

        $insert = array_diff($layoutss, $oldLayoutss);
        $delete = array_diff($oldLayoutss, $layoutss);
        $adapter = $this->getConnection();

        if (!empty($delete)) {
            $condition = ['layouts_id IN(?)' => $delete, 'cms_id=?' => $id];
            $adapter->delete($this->cmsLayoutsTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $tagId) {
                $data[] = [
                    'cms_id' => (int)$id,
                    'layouts_id' => (int)$tagId,
                    'position'  => 1
                ];
            }
            $adapter->insertMultiple($this->cmsLayoutsTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $layoutsIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'dbm_cmslayouts_cms_after_save_layoutss',
                ['cms' => $cms, 'layouts_ids' => $layoutsIds]
            );

            $cms->setIsChangedLayoutsList(true);
            $layoutsIds = array_keys($insert + $delete);
            $cms->setAffectedLayoutsIds($layoutsIds);
        }

        return $this;
    }

    /**
     * @param \Dbm\CmsLayouts\Model\Cms $cms
     *
     * @return array
     */
    public function getLayoutsIds(\Dbm\CmsLayouts\Model\Cms $cms)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->cmsLayoutsTable, 'layouts_id')
            ->where('cms_id = ?', (int)$cms->getId());

        return $adapter->fetchCol($select);
    }
}
