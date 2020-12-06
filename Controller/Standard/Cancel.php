<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Controller\Standard;

use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;

/**
 * Class Cancel
 * @package Spotii\Spotiipay\Controller\Standard
 */
class Cancel extends SpotiiPay
{
    /**
     * Cancel the order
     */
    public function execute()
    {
     try{
         
        //$order = $this->getOrder();
        $orderId = $this->getRequest()->getParam("id");
        $reference = $this->getRequest()->getParam("magento_spotii_id");
        $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
        $paymentSubmitted = $this->getRequest()->getParam("submitted");

        /*if($paymentSubmitted == '1'){

        $order->setState("canceled")->setStatus("canceled");
        $order->save();*/

        $this->messageManager->addError("<b>Order Cancelled!</b><br> Your payment with Spotii cannot be completed as requested.");
        $order->registerCancellation("Returned from Spotii with completeing payment, order canceled.");
        $this->spotiiHelper->logSpotiiActions("Returned from Spotii without completeing payment, order canceled.");
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout/onepage/failure')
        );
    /*}else{
        $order->registerCancellation("Abandoned Cart");
        $this->spotiiHelper->logSpotiiActions("Abandoned Cart");
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout/onepage/failure') 
        );
    }*/
     }catch (\Magento\Framework\Exception\LocalizedException $e) {
        $this->spotiiHelper->logSpotiiActions("Redirect Exception: cancel " . $e->getMessage());
        $this->messageManager->addError(
            $e->getMessage()
        );
      } catch (\Exception $e) {
        $this->spotiiHelper->logSpotiiActions("Redirect Exception: cancel " . $e->getMessage());
        $this->messageManager->addError(
            $e->getMessage()
        );
    }


    }
}