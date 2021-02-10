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
        $quote = $this->_checkoutSession->getQuote();
        $this->spotiiHelper->logSpotiiActions("Quote Id : " . $quote->getId());
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $this->spotiiHelper->logSpotiiActions("Customer Id : $customerId");
            $customer = $this->_customerRepository->getById($customerId);
            $quote->setCustomer($customer);
            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();
            if ((empty($shippingAddress) || empty($shippingAddress->getStreetLine(1))) && (empty($billingAddress) || empty($billingAddress->getStreetLine(1)))) {
                $json = $this->_jsonHelper->jsonEncode(["message" => "Please select an address"]);
                $jsonResult = $this->_resultJsonFactory->create();
                $jsonResult->setData($json);
                return $jsonResult;
            } elseif (empty($billingAddress) || empty($billingAddress->getStreetLine(1)) || empty($billingAddress->getFirstname())) {
                $quote->setBillingAddress($shippingAddress);
            }
        } else {
            $post = $this->getRequest()->getPostValue();
            $this->spotiiHelper->logSpotiiActions("Guest customer");
            if (!empty($post['email'])) {
                $quote->setCustomerEmail($post['email'])
                    ->setCustomerIsGuest(true)
                    ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
            }
        }
        $payment = $quote->getPayment();
        $payment->setMethod('spotiipay');
        $payment->setIsTransactionPending(true);
        $payment->save();
        $quote->reserveOrderId();
        $quote->setPayment($payment);
        $quote->save();
        $this->_checkoutSession->replaceQuote($quote);
        $checkoutUrl = $this->_spotiipayModel->getSpotiiCheckoutUrl($quote);
        $this->spotiiHelper->logSpotiiActions("Checkout Url : $checkoutUrl");
        try{
        $json = $this->_jsonHelper->jsonEncode(["redirectURL" => $checkoutUrl]);
        $jsonResult = $this->_resultJsonFactory->create();
        $jsonResult->setData($json);

        // Create "pending" order before redirect to Spotii
        $quoteId = $quote->getId();
              // **
        $quote->collectTotals()->save();   
        //$order = $this->_quoteManagement->submit($quote);
        $order = $this->_checkoutSession->getLastRealOrder();

        if($order){
            $this->spotiiHelper->logSpotiiActions("exists");
            //$this->spotiiHelper->logSpotiiActions($order->getId());
            //$order->save(); // **
            $this->_checkoutSession->setLastQuoteId($quoteId);
        }
        /*$invoiceCollection = $order->getInvoiceCollection();
        foreach($invoiceCollection as $invoice):
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);
            $this->invoiceRepository->save($invoice);
        endforeach;*/
        $reference = $payment->getAdditionalInformation('spotii_order_id');
        $this->_spotiipayModel->createTransaction(
            $order,
            $reference,
            \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER
        );
        
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
        return $jsonResult;
    }
}
