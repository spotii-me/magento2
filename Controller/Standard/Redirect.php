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
class Redirect extends SpotiiPay
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
        $this->spotiiHelper->logSpotiiActions("****Starting Spotii****");
        //$this->_checkoutSession->restoreQuote();
        $quote = $this->_checkoutSession->getQuote();
        $order = $this->_checkoutSession->getLastRealOrder();

        if($order->getId()){
            $this->spotiiHelper->logSpotiiActions("exists");
            $this->spotiiHelper->logSpotiiActions($order->getId());
            $this->spotiiHelper->logSpotiiActions($order->getState());
        //$this->spotiiHelper->logSpotiiActions("Quote Id : " . $order->getId());
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $this->spotiiHelper->logSpotiiActions("Customer Id : $customerId");
            $customer = $this->_customerRepository->getById($customerId);
            $order->setCustomer($customer);
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            if ((empty($shippingAddress) || empty($shippingAddress->getStreetLine(1))) && (empty($billingAddress) || empty($billingAddress->getStreetLine(1)))) {
                $json = $this->_jsonHelper->jsonEncode(["message" => "Please select an address"]);
                $jsonResult = $this->_resultJsonFactory->create();
                $jsonResult->setData($json);
                return $jsonResult;
            } elseif (empty($billingAddress) || empty($billingAddress->getStreetLine(1)) || empty($billingAddress->getFirstname())) {
                $order->setBillingAddress($shippingAddress);
            }
        } else {
            $post = $this->getRequest()->getPostValue();
            $this->spotiiHelper->logSpotiiActions("Guest customer");
            if (!empty($post['email'])) {
                $order->setCustomerEmail($post['email'])
                    ->setCustomerIsGuest(true)
                    ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            }
        } 
        try{
        $this->_checkoutSession->restoreQuote();
        //$payment = $order->getPayment();
        //$payment->setMethod('spotiipay');
        //$this->spotiiHelper->logSpotiiActions("payment method :". $payment->getMethod());
        //$payment->setIsTransactionPending(true);
        //$payment->save();
        //$order->reserveOrderId();
        //$order->setPayment($payment);
        $order->save();
        //$this->_checkoutSession->replaceQuote($order);
        $quoteId = $quote->getId();
        $checkoutUrl = $this->_spotiipayModel->getSpotiiCheckoutUrl($order,$quoteId);
        $this->spotiiHelper->logSpotiiActions("Checkout Url : $checkoutUrl");
       
        $json = $this->_jsonHelper->jsonEncode(["redirectURL" => $checkoutUrl]);
        $jsonResult = $this->_resultJsonFactory->create();
        $jsonResult->setData($json);

        // Create "pending" order before redirect to Spotii
        //$quoteId = $quote->getId();
              
        //$quote->collectTotals()->save();   
    
        $this->_checkoutSession->setLastQuoteId($quoteId);
        /*$invoiceCollection = $order->getInvoiceCollection();
        foreach($invoiceCollection as $invoice):
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);
            $this->invoiceRepository->save($invoice);
        endforeach;*/
        // $reference = $payment->getAdditionalInformation('spotii_order_id');
        // $this->_spotiipayModel->createTransaction(
        //     $order,
        //     $reference,
        //     \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER
        // );
        
        //$order->setState('new')->setStatus('pending');
       

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
        return $jsonResult;
    }
}
