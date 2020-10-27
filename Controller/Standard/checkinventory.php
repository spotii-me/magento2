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
        $availabilityAmount=$this->spotiiApiIdentity->getAvailabilityAmount();
        $post = $this->getRequest()->getPostValue();
        $itemsString = $post['items'];
        $items = $this->_jsonHelper->jsonDecode($itemsString);
        $flag = true;
        $isAvailable = true;
        foreach ($items as $item) {
            $sku = $item['sku'];
            $this->spotiiHelper->logSpotiiActions("checking ... " . $sku );
    
            $qtyOrdered = $item['qty'];
    
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $qtyInStock= $stockItem->getQty();
            if($qtyInStock < $qtyOrdered){
               $flag = false ;            
            }
            $product = $this->productRepository->get($sku);;
            if($product->getAttributeText('spotii_product')=="No"){
                $isAvailable=false;
            }
            }
            $json = $this->_jsonHelper->jsonEncode(["isInStock" => $flag,"isAvailableOnSpotii"=>$isAvailable, "availabilityAmount"=> $availabilityAmount]);
            $jsonResult = $this->_resultJsonFactory->create();
            $jsonResult->setData($json);
            return $jsonResult;
    }
}