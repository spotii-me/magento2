<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Source\Order;

//use Spotii\Spotiipay\Model\Config\Container\Container; extends Container
/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */
class OrderStatus extends OrderStatusInterface implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->statusCollectionFactory->create()->toOptionArray();        
    }
}
