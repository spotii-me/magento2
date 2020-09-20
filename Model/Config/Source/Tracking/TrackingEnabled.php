<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Tracking;

/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */
class TrackingEnabled implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'true',
                'label' => 'Yes',
            ],
            [
                'value' => 'false',
                'label' => 'No',
            ]
        ];
    }
}
