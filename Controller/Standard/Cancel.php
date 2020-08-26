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
        $order = $this->getOrder();
        $this->spotiiHelper->logSpotiiActions('items ' . $order->getAllVisibleItems());
        foreach ($order->getAllVisibleItems() as $item) {
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $decrease= $qty-($qty*2);
            $this->spotiiHelper->logSpotiiActions('sku ' . $sku .' Qty ' . $qty .' Decrease '.$decrease);
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $stockItem->setQty($decrease);
            //$stockItem->setIsInStock((bool)$qty); // this line
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
    }
}