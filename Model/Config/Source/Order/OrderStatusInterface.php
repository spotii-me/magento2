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
abstract class OrderStatusInterface
{

    protected $statusCollectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
    )
    {
        $this->statusCollectionFactory=$statusCollectionFactory;
    }
    
}