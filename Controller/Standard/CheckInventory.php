<?php

namespace Spotii\Spotiipay\Controller\Standard;

use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;

/**
 * Spotii Helper
 */
class CheckInventory extends SpotiiPay
{
    /**
     * Dump Spotii log actions
     *
     * @param string $msg
     * @return void
     */
    public function execute()
    {
        $items = $_POST['items'];
        $this->spotiiHelper->logSpotiiActions($items);
        $flag = true;
        foreach ($items as $item) {
            $this->spotiiHelper->logSpotiiActions("checking ... " . $item->getSku());
    
            $sku = $item->getSku();
            $qtyOrdered = $item->getQtyOrdered();
    
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $qtyInStock= $stockItem->getQty();
            if($qtyInStock < $qtyOrdered){
               $flag = false ;            
            }
            }
            $json = $this->_jsonHelper->jsonEncode(["isInStock" => $flag]);
            $jsonResult = $this->_resultJsonFactory->create();
            $jsonResult->setData($json);
            return $jsonResult;
    }
}
