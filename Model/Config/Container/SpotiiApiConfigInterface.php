<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Container;

use Magento\Store\Model\Store;

/**
 * Interface IdentityInterface
 * @package Spotii\Spotiipay\Model\Config\Container
 */
interface SpotiiApiConfigInterface extends IdentityInterface
{

    /**
     * Get public key
     * @return mixed
     */
    public function getPublicKey();

    /**
     * Get private key
     * @return mixed
     */
    public function getPrivateKey();

    /**
     * Get Payment mode
     * @return mixed
     */
    public function getPaymentMode();

    /**
     * Get Merchant Id
     * @return mixed
     */
    public function getMerchantId();

    /**
     * Get Spotii base url
     * @return mixed
     */
    public function getSpotiiBaseUrl();

    /**
     * Get Spotii auth base url
     * @return mixed
     */
    public function getSpotiiAuthBaseUrl();

    /**
     * Get log tracker status
     * @return mixed
     */
    public function isLogTrackerEnabled();

    /**
     * Get payment action
     * @return mixed
     */
    public function getPaymentAction();

    /**
     * Get new order status
     * @return mixed
     */
    public function getNewOrderStatus(\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory);
    
    /**
     * Get paid order status
     * @return mixed
     */
    public function getPaidOrderStatus(\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory);

    /**
     * Get canceled order status
     * @return mixed
     */
    public function getCanceledOrderStatus(\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory);
}
