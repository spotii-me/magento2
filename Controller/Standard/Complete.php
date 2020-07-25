<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Controller\Standard;

use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;
use Spotii\Spotiipay\Plugin\Sales\Controller\Adminhtml\Order\Invoice\SavePlugin;

/**
 * Class Complete
 * @package Spotii\Spotiipay\Controller\Standard
 */
class Complete extends SpotiiPay
{
    /**
     * Complete the order
     */
    public function execute()
    {
        $this->spotiiHelper->logSpotiiActions("Complete :: capture()");
        
        $this->spotiiHelper->logSpotiiActions("oid real:" . $this->_checkoutSession->getLastRealOrderId());
        $this->spotiiHelper->logSpotiiActions("oid:" . $this->_checkoutSession->getLastRealId());

        $redirect = 'checkout/cart';
        try {
            $this->spotiiHelper->logSpotiiActions("Returned from Spotiipay.");

            // $oid1 = $this->getOrder();
            // $oid2 = $this->getOrder2();

            // $this->spotiiHelper->logSpotiiActions("oid real:" . $this->_checkoutSession->getLastRealOrderId());
            // $this->spotiiHelper->logSpotiiActions("oid:" . $this->_checkoutSession->getLastRealId());

            $quoteId = $this->_checkoutSession->getLastQuoteId();   // worked
            $oid = $this->_checkoutSession->getLastOrderId();
            $oidReal = $this->_checkoutSession->getLastRealOrderId();
            $this->spotiiHelper->logSpotiiActions("qid " . $quoteId . ", oid " . $oid . ", " . $oidReal);

            $orderId = $this->getRequest()->getParam("id");  //worked
            $orId = $this->getRequest()->getParam("order_id");
            $this->spotiiHelper->logSpotiiActions("id " . $orderId . ", orId " . $orId);


            $order = $this->_orderFactory->create()->load($orderId);
            // $quote = $this->_quoteFactory->create()->load($quoteId);
            $quote = $this->_quoteRepository->get($quoteId);

            // $orderId = 163;
            // $this->spotiiHelper->logSpotiiActions("inc id ", $order->getIncrementId());
            // $order   = Mage::getModel('sales/order')->load($o3);


            // $quote = $this->_checkoutSession->getQuote();

            $this->spotiiHelper->logSpotiiActions("quote id: $quote->getId()");


            $payment = $quote->getPayment();
            $reference = $payment->getAdditionalInformation(\Spotii\Spotiipay\Model\SpotiiPay::ADDITIONAL_INFORMATION_KEY_ORDERID);
            $orderId = $quote->getReservedOrderId();
            // $this->spotiiHelper->logSpotiiActions("Order ID from quote : $orderId.");

            // $this->_checkoutSession
            //     ->setLastQuoteId($quote->getId())
            //     ->setLastSuccessQuoteId($quote->getId())
            //     ->clearHelperData();
            // $this->spotiiHelper->logSpotiiActions("Set data on checkout session");
            
            // $quote->collectTotals()->save();
            // $this->spotiiHelper->logSpotiiActions("**Saved Data on Quote**");
            // $order = $this->_quoteManagement->submit($quote);
            // $this->spotiiHelper->logSpotiiActions("**Quote Updated**");
            // $this->spotiiHelper->logSpotiiActions("Order created");

            $order = $this->getOrder();
            // $this->spotiiHelper->logSpotiiActions("got order: $order");

            if ($order) {
                $this->_checkoutSession->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
                $this->_spotiipayModel->createTransaction($order, $reference, $quote);
                $this->spotiiHelper->logSpotiiActions("Created transaction with reference $reference");

                // send email
                try {
                    $this->_orderSender->send($order);
                } catch (\Exception $e) {
                    $this->_helper->debug("Transaction Email Sending Error: " . json_encode($e));
                }

                $this->messageManager->addSuccess("Spotiipay Transaction Completed");

                // -------------------------------
                $reference = $payment->getAdditionalInformation(self::ADDITIONAL_INFORMATION_KEY_ORDERID);

                $result = $this->getSpotiiOrderInfo($reference);
                $payment->setAdditionalInformation('payment_type', $this->getConfigData('payment_action'));
                $this->spotiiCapture($reference);
                $payment->setTransactionId($reference)->setIsTransactionClosed(false);


                $this->spotiiHelper->logSpotiiActions("setting order status as paymentAuthorised...");
                $order->setState("paymentauthorised")->setStatus("paymentauthorised");
                $order->save();
                $this->spotiiHelper->logSpotiiActions("done setting order status as paymentAuthorised...");
                
                $redirect = 'checkout/onepage/success';
                $orderdetails = $this->order->loadByIncrementId($order->getId());
                foreach ($orderdetails->getInvoiceCollection() as $invoice) {
                    SavePlugin::handleCaptureAction($invoice);
                }

                // -------------------------------
            }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->spotiiHelper->logSpotiiActions("Transaction Exception: " . $e->getMessage());
            $this->messageManager->addError(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->spotiiHelper->logSpotiiActions("Transaction Exception: " . $e->getMessage());
            $this->messageManager->addError(
                $e->getMessage()
            );
        }
        $this->_redirect($redirect);
    }

    public function spotiiCapture($reference)
    {
        try {
            $this->spotiiHelper->logSpotiiActions("****Capture at Spotii Start****");
            $url = $this->spotiiApiIdentity->getSpotiiBaseUrl() . '/api/v1.0/orders' . '/' . $reference . '/capture' . '/';
            $authToken = $this->spotiiApiConfig->getAuthToken();
            $response = $this->spotiiApiProcessor->call(
                $url,
                $authToken,
                null,
                \Magento\Framework\HTTP\ZendClient::POST
            );
            $this->spotiiHelper->logSpotiiActions("****Capture at Spotii End****");
        } catch (\Exception $e) {
            $this->spotiiHelper->logSpotiiActions($e->getMessage());
            throw new LocalizedException(__($e->getMessage()));
        }
        return $response;
    }

    public function getSpotiiOrderInfo($reference)
    {
        $this->spotiiHelper->logSpotiiActions("****Getting order from Spotii****");
        $url = $this->spotiiApiIdentity->getSpotiiBaseUrl() . '/api/v1.0/orders' . '/' . $reference . '/';
        $authToken = $this->spotiiApiConfig->getAuthToken();
        $result = $this->spotiiApiProcessor->call(
            $url,
            $authToken,
            null,
            \Magento\Framework\HTTP\ZendClient::GET
        );
        $result = $this->jsonHelper->jsonDecode($result, true);
        if (isset($result['status']) && $result['status'] == \Spotii\Spotiipay\Model\Api\ProcessorInterface::BAD_REQUEST) {
            throw new LocalizedException(__('Invalid checkout. Please retry again.'));
            return $this;
        }
        $this->spotiiHelper->logSpotiiActions("****Order successfully fetched from Spotii****");
        return $result;
    }
}
