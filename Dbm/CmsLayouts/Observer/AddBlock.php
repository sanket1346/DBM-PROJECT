<?php

namespace Dbm\CmsLayouts\Observer;

use Dbm\CmsLayouts\Block\Layouts;
use Dbm\CmsLayouts\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;

/**
 * Class AddBlock
 * @package Dbm\AutoRelated\Observer
 */
class AddBlock implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * AddBlock constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'content' => 'content',
        ], true);

        if ($type !== false) {
            /** @var Layout $layout */
            $layout = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();
            $output = $observer->getTransport()->getOutput();

            foreach ($this->helperData->getActiveLayoutss() as $layouts) {

                $locations = explode(',', $layouts->getLocation());

                //print_r($layouts->getData());
                //exit;

                foreach ($locations as $value) {

                    $datas = explode('.', $value);
                    $pageType = $datas[0];
                    $location = 'content-top';

                    if (($fullActionName === $pageType)) {

                        $content = $layout->createBlock(Layouts::class)
                            ->setLayouts($layouts)
                            ->toHtml();

                        if (strpos($location, 'top') !== false) {
                            $output = "<div id=\"dbm-cmslayouts-block-before-{$layouts->getId()}\">
                                        $content</div>" . $output;
                        } else {
                            $output .= "<div id=\"dbm-cmslayouts-block-after-{$layouts->getId()}\">
                                        $content</div>";
                        }
                    }
                }
            }

            $observer->getTransport()->setOutput($output);
        }

        return $this;
    }
}
