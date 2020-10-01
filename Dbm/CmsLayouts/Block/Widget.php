<?php


namespace Dbm\CmsLayouts\Block;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Widget
 * @package Dbm\CmsLayouts\Block
 */
class Widget extends Layouts
{
    /**
     * @return array|bool|AbstractCollection
     */
    public function getCmsCollection()
    {
        $layoutsId = $this->getData('layouts_id');
        if (!$layoutsId || !$this->helperData->isEnabled()) {
            return [];
        }

        $layoutsCollection = $this->helperData->getActiveLayoutss();
        $layouts = $layoutsCollection->addFieldToFilter('layouts_id', $layoutsId)->getFirstItem();
        $this->setLayouts($layouts);

        return parent::getCmsCollection();
    }
}
