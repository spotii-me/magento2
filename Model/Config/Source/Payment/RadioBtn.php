<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Source\Payment;

/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */

class RadioBtn implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'base',
                'label' => 'Base Currency',
            ],
            [
                'value' => 'order',
                'label' => 'Order Currency',
            ]
        ];
    }
}
