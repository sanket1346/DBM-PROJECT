<?php

namespace Dbm\CmsLayouts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Dbm\CmsLayouts\Model\ResourceModel\Layouts\CollectionFactory;

/**
 * Class Layoutss
 * @package Dbm\CmsLayouts\Model\Config\Source
 */
class Layoutss implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Layoutss constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        $options = [];

        $rules = $this->collectionFactory->create()->addActiveFilter();
        foreach ($rules as $rule) {
            $options[$rule->getId()] = $rule->getName();
        }

        return $options;
    }
}
