<?php

namespace Dbm\CmsLayouts\Block;

use Dbm\CmsLayouts\Helper\Data as cmsHelper;
use Exception;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Layouts
 * @package Dbm\CmsLayouts\Block
 */
class Layouts extends Template
{
    /**
     * @type cmsHelper
     */
    public $helperData;

    /**
     * @type StoreManagerInterface
     */
    protected $store;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var FilterProvider
     */
    public $filterProvider;

    protected $objectManager;

    protected $templateProcessor;

    /**
     * Layouts constructor.
     *
     * @param Template\Context $context
     * @param cmsHelper $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param DateTime $dateTime
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        cmsHelper $helperData,
        CustomerRepositoryInterface $customerRepository,
        DateTime $dateTime,
        FilterProvider $filterProvider,
        ObjectManagerInterface $objectManager,
        Json $serializer = null,
        \Zend_Filter_Interface $templateProcessor,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->customerRepository = $customerRepository;
        $this->store = $context->getStoreManager();
        $this->_date = $dateTime;
        $this->objectManager = $objectManager;
        $this->filterProvider = $filterProvider;
        $this->templateProcessor = $templateProcessor;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context, $data);
    }

    public function filterOutputHtml($string)
    {
        return $this->templateProcessor->filter($string);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Dbm_CmsLayouts::cmslayouts.phtml');
    }

    /**
     * Get Layouts Id
     * @return string
     */
    public function getLayoutsId()
    {
        if ($this->getLayouts()) {
            return $this->getLayouts()->getLayoutsId();
        }

        return time();
    }

    /**
     * @param $content
     *
     * @return string
     * @throws Exception
     */
    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    /**
     * @return array|AbstractCollection
     */
    public function getCmsCollection()
    {
        $collection = [];
        if ($this->getLayoutsId()) {
            $collection = $this->helperData->getCmsCollection($this->getLayoutsId())->addFieldToFilter('status', 1);
        }

        return $collection;
    }

    public function getFieldUnserializer($fieldVal)
    {
        return $this->serializer->unserialize($fieldVal);
    }

    /**
     * @return false|string
     */
    public function getCmsOptions()
    {
        return $this->helperData->getCmsOptions($this->getLayouts());
    }
}
