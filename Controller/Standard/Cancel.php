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

        if($paymentSubmitted == '1'){
        $order->setState("canceled")->setStatus("canceled");
        $order->save();
        foreach ($order->getAllVisibleItems() as $item) {

            $sku = $item->getSku();
            $qtyOrdered = $item->getQtyOrdered();

            $stockItem = $this->stockRegistry->getStockItemBySku($sku);

            $qtyInStock= $stockItem->getQty();
            $finalQty = $qtyInStock +$qtyOrdered;

            $stockItem->setQty($finalQty);
            $stockItem->setIsInStock((bool)$finalQty);
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            $this->spotiiHelper->logSpotiiActions('result' . $this->stockRegistry->updateStockItemBySku($sku, $stockItem));
        }
        
        $this->messageManager->addError("Spotiipay Transaction failed");
        $order->registerCancellation("Spotiipay transaction failed.");

        $this->spotiiHelper->logSpotiiActions("Returned from Spotiipay without completing payment. Order cancelled.");
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout/onepage/failure')
        );
    }else{
        $this->messageManager->addError("Spotiipay Transaction was not initiated");
        $order->registerCancellation("No attempt of payment Spotiipay. Order cancelled.");
        $this->spotiiHelper->logSpotiiActions( "No attempt of payment Spotiipay. Order cancelled." );
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout/onepage/failure') 
        );
    }
     }catch (\Magento\Framework\Exception\LocalizedException $e) {
        $this->spotiiHelper->logSpotiiActions("Redirect Exception: " . $e->getMessage());
        $this->messageManager->addError(
            $e->getMessage()
        );
      } catch (\Exception $e) {
        $this->spotiiHelper->logSpotiiActions("Redirect Exception: " . $e->getMessage());
        $this->messageManager->addError(
            $e->getMessage()
        );
    }


    }
}