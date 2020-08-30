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
class OrderStatus 
{
    /**
     * @return array
     */
    public function getStatus($statusCollectionFactory)
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();        
        return $options;
    }
}
