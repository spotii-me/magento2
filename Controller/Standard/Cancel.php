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

        $this->spotiiHelper->logSpotiiActions('items ' . sizeof($order->getAllVisibleItems()));
        foreach ($order->getAllVisibleItems() as $item) {

            $sku = $item->getSku();
            $qtyOrdered = $item->getQtyOrdered();
            $this->spotiiHelper->logSpotiiActions('sku ' . $sku .' Qty ' . $qtyOrdered);

            $stockItem = $this->stockRegistry->getStockItemBySku($sku);

            $qtyInStock= $stockItem->getQty();
            $finalQty = $qtyInStock +$decrease;

            $stockItem->setQty($finalQty);
            $stockItem->setIsInStock((bool)$finalQty);
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            $this->spotiiHelper->logSpotiiActions('result' . $this->stockRegistry->updateStockItemBySku($sku, $stockItem));
        }
        $this->messageManager->addError("Spotiipay Transaction failed");
        $order->registerCancellation("Returned from Spotiipay without completing payment.");
        $this->spotiiHelper->logSpotiiActions(
            "Returned from Spotiipay without completing payment. Order cancelled."
        );
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->_url->getUrl('checkout/onepage/failure')
       );
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