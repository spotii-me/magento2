<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Source\Store;

//use Spotii\Spotiipay\Model\Config\Container\Container; extends Container
/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */
abstract class StoreIdInterface {

    protected $_storeManager;
    
public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager
) {

     $this->_storeManager = $storeManager;
}

}