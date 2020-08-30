<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Source\Order;

/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */
class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'pending',
                'label' => 'pending',
            ],
            [
                'value' => 'paymentauthorized',
                'label' => 'paymentauthorized',
            ],
            [
                'value' => 'canceled',
                'label' => 'canceled',
            ]
        ];
    }
}
