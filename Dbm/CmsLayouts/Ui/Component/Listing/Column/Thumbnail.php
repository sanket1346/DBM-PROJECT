<?php


namespace Dbm\CmsLayouts\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Dbm\CmsLayouts\Model\Config\Source\Image;
use Dbm\CmsLayouts\Model\Config\Source\Type;

/**
 * Class Thumbnail
 * @package Dbm\CmsLayouts\Ui\Component\Listing\Column
 */
class Thumbnail extends Column
{
    /**
     * @var Image
     */
    protected $imageModel;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Thumbnail constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageModel
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageModel,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->imageModel = $imageModel;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $path = $this->imageModel->getBaseUrl();
            foreach ($dataSource['data']['items'] as & $item) {
                $cms = new DataObject($item);
                if ($item['type'] == Type::IMAGE && $item['image']) {
                    $item[$fieldName . '_src'] = $path . $item['image'];
                }

                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'cmslayouts/cms/edit',
                    ['cms_id' => $cms->getCmsId(), 'store' => $this->context->getRequestParam('store')]
                );
            }
        }

        return $dataSource;
    }
}
