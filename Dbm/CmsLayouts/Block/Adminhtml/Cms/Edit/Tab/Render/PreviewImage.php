<?php
namespace Dbm\CmsLayouts\Block\Adminhtml\Cms\Edit\Tab\Render;

use Dbm\CmsLayouts\Helper\Data as cmsHelper;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class PreviewImage extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    /**
     * @type cmsHelper
     */
    public $helperData;

    const DEMO1 = 'fullwidth.png';
    const DEMO2 = '2column.png';
    const DEMO3 = '3column.png';
    const DEMO4 = '4column.png';
    const DEMO5 = 'feature_product.png';
    const DEMO6 = 'top_seller.png';

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        AuthorizationInterface $authorization,
        cmsHelper $helperData,
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->helperData = $helperData;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        $mediaUrl = $this->helperData->getMediaUrl();
        $html = "<img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO1 . "' data-value ='1' class='hidden' />
                 <img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO2 . "' data-value ='2' class='hidden'/>
                 <img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO3 . "' data-value ='3' class='hidden'/>
                 <img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO4 . "' data-value ='4' class='hidden'/>
                 <img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO5 . "' data-value ='5' class='hidden'/>
                 <img src='" . $mediaUrl . 'dbm/cmslayouts/cms/demo/' . self::DEMO6 . "' data-value ='6' class='hidden'/>";

        return $html;
    }
}
