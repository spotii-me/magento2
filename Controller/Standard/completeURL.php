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
    try{

        //$orderId = $this->getRequest()->getParam("id");
      /* $order=$this->getOrder();
       $orderId = $order->getId();
       $this->spotiiHelper->logSpotiiActions("orderId: " . $orderId);
       //$reference = $order->getPayment()->getAdditionalInformation('spotii_order_id');
       //$this->spotiiHelper->logSpotiiActions("reference: " . $reference1);


       $orderId1 = $this->getRequest()->getParam("id");
       $order1 = $this->_orderFactory->create()->loadByIncrementId($orderId1);
       $this->spotiiHelper->logSpotiiActions("orderId1: " . $orderId1);
       $reference1 = $this->getRequest()->getParam("magento_spotii_id"); 
       $this->spotiiHelper->logSpotiiActions("reference1: " . $reference1);*/
       $url =$this->apiPayloadBuilder->getCompleteUrlUniv();
       $this->spotiiHelper->logSpotiiActions("URLComplete: " . $url);

       return $url;


    }catch (\Exception $e) {
        $this->spotiiHelper->logSpotiiActions("completeUrl Exception: " . $e->getMessage());
        $this->messageManager->addError(
            $e->getMessage()
        );
    }
        return $completeUrl;
    }

}
