<?php


namespace Dbm\CmsLayouts\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Effect
 * @package Dbm\CmsLayouts\Model\Config\Source
 */
class Effect implements ArrayInterface
{
    const LAYOUTS = 'Layouts';
    const FADE_OUT = 'fadeOut';
    const ROTATE_OUT = 'rotateOut';
    const FLIP_OUT = 'flipOutX';
    const ROLL_OUT = 'rollOut';
    const ZOOM_OUT = 'zoomOut';
    const LAYOUTS_OUT_LEFT = 'layoutOutLeft';
    const LAYOUTS_OUT_RIGHT = 'layoutOutRight';
    const LIGHT_SPEED_OUT = 'lightSpeedOut';

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::LAYOUTS,
                'label' => __('No')
            ],
            [
                'value' => self::FADE_OUT,
                'label' => __('fadeOut')
            ],
            [
                'value' => self::ROTATE_OUT,
                'label' => __('rotateOut')
            ],
            [
                'value' => self::FLIP_OUT,
                'label' => __('flipOut')
            ],
            [
                'value' => self::ROLL_OUT,
                'label' => __('rollOut')
            ],
            [
                'value' => self::ZOOM_OUT,
                'label' => __('zoomOut')
            ],
            [
                'value' => self::LAYOUTS_OUT_LEFT,
                'label' => __('layoutOutLeft')
            ],
            [
                'value' => self::LAYOUTS_OUT_RIGHT,
                'label' => __('layoutOutRight')
            ],
            [
                'value' => self::LIGHT_SPEED_OUT,
                'label' => __('lightSpeedOut')
            ],
        ];

        return $options;
    }
}
