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
        $this->spotiiHelper->logSpotiiActions("--------");

        $redirect = 'checkout/cart';
        try {
            $this->spotiiHelper->logSpotiiActions("Returned from Spotiipay.");

            // -----------------------
            $quoteId = $this->_checkoutSession->getLastQuoteId();
            $orderId = $this->getRequest()->getParam("id");
            $reference = $this->getRequest()->getParam("magento_spotii_id");
            $this->spotiiHelper->logSpotiiActions("orderId: " . $orderId . ", quoteId: " . $quoteId . ", reference: " . $reference);

            // $this->spotiiCapture($reference);

            $this->_spotiipayModel->spotiiCapture($reference);

            // $orderId = 163;
            // $this->spotiiHelper->logSpotiiActions("inc id ", $order->getIncrementId());
            // $order   = Mage::getModel('sales/order')->load($o3);
            // $quote = $this->_checkoutSession->getQuote();

            // $order = $this->_orderFactory->create()->load($orderId);

            // $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);


            // $this->spotiiHelper->logSpotiiActions("currency: " . $order->getOrderCurrencyCode());
            // $this->spotiiHelper->logSpotiiActions("status: " .  $order->getStatus());
            $this->spotiiHelper->logSpotiiActions("id: " .  $order->getId());


            // send email
            // try {
            //     $this->_orderSender->send($order);
            // } catch (\Exception $e) {
            //     $this->_helper->debug("Transaction Email Sending Error: " . json_encode($e));
            // }

            $redirect = 'checkout/onepage/success';
            $this->_redirect($redirect);
            return;

            $order->setState("paymentauthorised")->setStatus("paymentauthorised");
            $order->save();
            $redirect = 'checkout/onepage/success';
            $this->_redirect($redirect);
            return;

            // -----------------------

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
