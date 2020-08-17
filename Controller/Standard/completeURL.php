<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Controller\Standard;

use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;

/**
 * Class Redirect
 * @package Spotii\Spotiipay\Controller\Standard
 */
class completeURL extends SpotiiPay
{

    /**
     * Redirection
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */


    public function execute()
    {

        $orderId=$this->getOrder()->getId();
        $reference = $this->getOrder()->getPayment()->getAdditionalInformation(SpotiiPay::ADDITIONAL_INFORMATION_KEY_ORDERID);
        $completeUrl = $this->spotiiApiConfig->getCompleteUrl($orderId, $reference);
        return $completeUrl;
    }

}
