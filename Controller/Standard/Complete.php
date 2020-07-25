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
        $redirect = 'checkout/cart';
        try {
            $this->spotiiHelper->logSpotiiActions("Returned from Spotiipay.");
            $quote = $this->_checkoutSession->getQuote();
            $payment = $quote->getPayment();
            $reference = $payment->getAdditionalInformation(\Spotii\Spotiipay\Model\SpotiiPay::ADDITIONAL_INFORMATION_KEY_ORDERID);
            $orderId = $quote->getReservedOrderId();
            $this->spotiiHelper->logSpotiiActions("Order ID from quote : $orderId.");

            $this->_checkoutSession
                ->setLastQuoteId($quote->getId())
                ->setLastSuccessQuoteId($quote->getId())
                ->clearHelperData();
            $this->spotiiHelper->logSpotiiActions("Set data on checkout session");
            
            $quote->collectTotals()->save();
            $this->spotiiHelper->logSpotiiActions("**Saved Data on Quote**");
            $order = $this->_quoteManagement->submit($quote);
            $this->spotiiHelper->logSpotiiActions("**Quote Updated**");
            $this->spotiiHelper->logSpotiiActions("Order created");

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
                
                $this->spotiiHelper->logSpotiiActions("setting order status as paymentAuthorised...");
                $order->setState("paymentAuthorised")->setStatus("paymentAuthorised");
                $order->save();
                $this->spotiiHelper->logSpotiiActions("done setting order status as paymentAuthorised...");
                
                $redirect = 'checkout/onepage/success';
                $orderdetails = $this->order->loadByIncrementId($order->getId());
                foreach ($orderdetails->getInvoiceCollection() as $invoice) {
                    SavePlugin::handleCaptureAction($invoice);
                }
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
}
