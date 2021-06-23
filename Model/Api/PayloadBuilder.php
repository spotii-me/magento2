<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Api;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PayloadBuilder
 * @package Spotii\Spotiipay\Model\Api
 */
class PayloadBuilder
{
    const PRECISION = 4;

    /**
     * @var ConfigInterface
     */
    private $spotiiApiConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * PayloadBuilder constructor.
     * @param ConfigInterface $spotiiApiConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $spotiiApiConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Build Spotii Checkout Payload
     * @param $quote
     * @param $reference
     * @return array
     */
    public function buildSpotiiCheckoutPayload($quote, $reference)
    {


        /**
         *{
            *  "type": "standard",
            *  "shopper": {
            *    "first_name": "testsdfsdf",
            *    "last_name": "tessdfsdfsdft",
            *    "phone": "0400000000",
            *    "email": "danzip@zip.co",
            *    "billing_address": {
            *      "line1": "8 dd",
            *      "city": "yssdd",
            *      "postal_code": "2199",
            *      "state": "NSW",
            *      "country": "ZA"
            *    }
            *  },
            *  "order": {
            *    "reference": "98801299778",
            *    "amount": 250,
            *    "currency": "BHD",
            *    "shipping": {
            *      "pickup": false,
            *      "address": {
            *        "line1": "8 dd",
            *        "city": "yssdd",
            *        "postal_code": "2199",
            *        "state": "NSW",
            *        "country": "ZA"
            *      }
            *    },
            *    "items": [
            *      {
            *        "name": "Argus All-Weather Tank",
            *        "amount": 23.54,
            *        "reference": "99",
            *        "quantity": 3,
            *        "type": "sku",
            *        "image_uri": "http://127.0.0.1/magento22/pub/static/version1594351058/frontend/Magento/luma/en_AU/Magento_Catalog/images/product/placeholder/.jpg",
            *        "item_uri": "http://127.0.0.1/magento22/argus-all-weather-tank",
            *        "product_code": "MT07-S-Gray"
            *      },
            *      {
            *        "name": "Shipping",
            *        "amount": 16.05,
            *        "reference": "Shipping",
            *        "quantity": 1,
            *        "type": "shipping"
            *      }
            *    ],
            *    "cart_reference": "90"
            *  },
            *  "metadata": {},
            *  "config": {
            *    "redirect_uri": "https://global-api.labs.au.edge.zip.co/merchant/callback?redirect=http%3A%2F%2F127.0.0.1%2Fmagento22%2Fzippayment%2Fcomplete%2F&region=ae"
            *  }
            *} 
        **/
        $billingPayload = $this->buildBillingPayload($quote);
        $customerPayload = $this->buildCustomerPayload($quote,$billingPayload);
        
        $itemPayload = $this->buildItemPayload($quote);
        $shippingAddressPayload = $this->buildShippingPayload($quote);
        $orderPayload = $this->buildOrderPayload($quote, $reference,$shippingAddressPayload,$itemPayload);
        $payload = array_merge_recursive(
            $orderPayload,
            $customerPayload
        );
        $config = [
            "redirect_uri"=>"https://global-api.labs.au.edge.zip.co/merchant/callback?redirect=http%3A%2F%2F127.0.0.1%2Fmagento22%2Fzippayment%2Fcomplete%2F&region=ae"
        ];   
        $payload["type"]= "standard";
        $payload["config"]= $config;
        $payload["metadata"]= [];

        //$payload["completes"] = true;
        return $payload;
    }

    /**
     * Build Checkout Payload from Magento Checkout
     * @param $quote
     * @param $reference
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function buildCheckoutPayload($quote, $reference)
    {
        $orderId = $quote->getReservedOrderId();
        $completeUrl = $this->spotiiApiConfig->getCompleteUrl($orderId, $reference, $quote->getId());
        $cancelUrl = $this->spotiiApiConfig->getCancelUrl($orderId, $reference);
        $checkoutPayload["total"] = strval(round($quote->getGrandTotal(), self::PRECISION));
        $checkoutPayload["currency"] = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $checkoutPayload["description"] = $reference;
        $checkoutPayload["reference"] = $reference;
        $checkoutPayload["display_reference"] = $orderId;
        $checkoutPayload["reject_callback_url"] = $cancelUrl;
        $checkoutPayload["confirm_callback_url"] = $completeUrl;
        return $checkoutPayload;
    }

    /**
     * Build Order Payload
     * @param $quote
     * @return mixed
     */
    private function buildOrderPayload($quote, $reference,$shippingAddressPayload,$itemPayload)
    {
        /**
            *   {
            *    "reference": "98801299778",
            *    "amount": 250,
            *    "currency": "BHD",
            *    "shipping": {
            *      "pickup": false,
            *      "address": {
            *        "line1": "8 dd",
            *        "city": "yssdd",
            *        "postal_code": "2199",
            *        "state": "NSW",
            *        "country": "ZA"
            *      }
            *    },
            *    "items": [
            *      {
            *        "name": "Argus All-Weather Tank",
            *        "amount": 23.54,
            *        "reference": "99",
            *        "quantity": 3,
            *        "type": "sku",
            *        "image_uri": "http://127.0.0.1/magento22/pub/static/version1594351058/frontend/Magento/luma/en_AU/Magento_Catalog/images/product/placeholder/.jpg",
            *        "item_uri": "http://127.0.0.1/magento22/argus-all-weather-tank",
            *        "product_code": "MT07-S-Gray"
            *      },
            *      {
            *        "name": "Shipping",
            *        "amount": 16.05,
            *        "reference": "Shipping",
            *        "quantity": 1,
            *        "type": "shipping"
            *      }
            *    ],
            *    "cart_reference": "90"
            *  },
        */
        $orderPayload["order"] = [
            "amount" => strval(round($quote->getGrandTotal(), self::PRECISION)),
            "currency" => $this->storeManager->getStore()->getCurrentCurrencyCode(),
            "reference" => $reference,
            "shipping" => $shippingAddressPayload,
            "items" => $itemPayload,
            "cart_reference"=> "90"
        ];
        return $orderPayload;
    }

    /**
     * Build Customer Payload
     * @param $quote
     * @return mixed
     */
    private function buildCustomerPayload($quote, $billingAddress)
    {

        /** 
        *  "shopper": {
            *    "first_name": "testsdfsdf",
            *    "last_name": "tessdfsdfsdft",
            *    "phone": "0400000000",
            *    "email": "danzip@zip.co",
            *    "billing_address": {
            *      "line1": "8 dd",
            *      "city": "yssdd",
            *      "postal_code": "2199",
            *      "state": "NSW",
            *      "country": "ZA"
            *    }
            *  },
        */
        $billingAddress = $quote->getBillingAddress();
        $customerPayload["shopper"] = [
            "first_name" => $quote->getCustomerFirstname() ? $quote->getCustomerFirstname() : $billingAddress->getFirstname(),
            "last_name" => $quote->getCustomerLastname() ? $quote->getCustomerLastname() : $billingAddress->getLastname(),
            "email" => $quote->getCustomerEmail(),
            "phone" => $billingAddress->getTelephone(),
            "billing_address" => $billingAddress
        ];
        return $customerPayload;
    }

    /**
     * Build Billing Address Payload
     * @param $quote
     * @return mixed
     */
    private function buildBillingPayload($quote)
    {   /** 
        *    "billing_address": {
            *      "line1": "8 dd",
            *      "city": "yssdd",
            *      "postal_code": "2199",
            *      "state": "NSW",
            *      "country": "ZA"
            *    }
        */
        $billingAddress = $quote->getBillingAddress();
        $billingPayload = [
            "line1" => $billingAddress->getStreetLine(1).' ' .$billingAddress->getStreetLine(2),
            "city" => $billingAddress->getCity(),
            "postal_code" => $billingAddress->getPostcode(),
            "state" => $billingAddress->getRegionCode(),
            "country" => $billingAddress->getCountryId()
        ];
        return $billingPayload;
    }

    /**
     * Build Shipping Address Payload
     * @param $quote
     * @return mixed
     */
    private function buildShippingPayload($quote)
    {
        /**
        *  "shipping": {
        *      "pickup": false,
        *      "address": {
        *        "line1": "8 dd",
        *        "city": "yssdd",
        *        "postal_code": "2199",
        *        "state": "NSW",
        *        "country": "ZA"
        *      }
        *    }
        * 
        */
        $shippingAddress = $quote->getShippingAddress();
        $shippingPayload["pickup"] = False;
        $shippingPayload["address"] = [
            "line1" => $shippingAddress->getStreetLine(1).' '.$shippingAddress->getStreetLine(2),
            "city" => $shippingAddress->getCity(),
            "postal_code" => $shippingAddress->getPostcode(),
            "state" => $shippingAddress->getRegionCode(),
            "country" => $shippingAddress->getCountryId(),
           
        ];
        return $shippingPayload;
    }

    /**
     * Build Cart Item Payload
     * @param $quote
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function buildItemPayload($quote)
    {
        /**
        *     "items": [
        *      {
        *        "name": "Argus All-Weather Tank",
        *        "amount": 23.54,
        *        "reference": "99",
        *        "quantity": 3,
        *        "type": "sku",
        *        "image_uri": "http://127.0.0.1/magento22/pub/static/version1594351058/frontend/Magento/luma/en_AU/Magento_Catalog/images/product/placeholder/.jpg",
        *        "item_uri": "http://127.0.0.1/magento22/argus-all-weather-tank",
        *        "product_code": "MT07-S-Gray"
        *      },
        *      {
        *        "name": "Shipping",
        *        "amount": 16.05,
        *        "reference": "Shipping",
        *        "quantity": 1,
        *        "type": "shipping"
        *      }
        *    ],
        */
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $itemPayload = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $productName = $item->getName();
            $productSku = $item->getSku();
            $productQuantity = $item->getQtyOrdered();
            $itemData = [
                "name" => $productName,
                "amount" => strval(round($item->getPriceInclTax(), self::PRECISION)),
                "reference" => $productSku,
                "quantity" => $productQuantity,
                "type" => "sku",
                "currency" => $currencyCode,
                "image_uri" => "http://127.0.0.1/magento22/pub/static/version1594351058/frontend/Magento/luma/en_AU/Magento_Catalog/images/product/placeholder/.jpg",
                "item_uri"=> "http://127.0.0.1/magento22/argus-all-weather-tank",
                "product_code"=> "MT07-S-Gray"
            ];
            $shippingData = [
                "name"=> "Shipping",
                "amount"=> 16.05,
                "reference"=> "Shipping",
                "quantity"=> $productQuantity,
                "type"=> "shipping"
            ];
            $itemPayloadItem=array_merge_recursive(
                $itemData,
                $shippingData
            );
            array_push($itemPayload, $itemPayloadItem);
        }
        return $itemPayload;
    }
}
