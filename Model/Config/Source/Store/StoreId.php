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
class StoreId extends StoreIdInterface implements \Magento\Framework\Option\ArrayInterface
{

public function toOptionArray(){

    $storeManagerDataList = $this->_storeManager->getStores();
    $options = array();
     
     foreach ($storeManagerDataList as $key => $value) {
               $options[] = ['label' => $value['name'].' - '.$value['code'], 'value' => $key];
     }
     return $options;   
}
}