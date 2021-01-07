<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Controller\Standard;

use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;

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
        $redirect = 'checkout/onepage/success';
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
                }; 

                $this->_checkoutSession->setLastSuccessQuoteId($quoteId);
                $this->_checkoutSession->setLastQuoteId($quoteId);
                $this->_checkoutSession->setLastOrderId($order->getEntityId());
                $this->messageManager->addSuccess("<b>Success! Payment completed!</b><br>Thank you for your payment, your order with Spotii has been placed.");
                $invoiceCollection = $order->getInvoiceCollection();
                foreach($invoiceCollection as $invoice):
                    $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
                    $this->invoiceRepository->save($invoice);
                endforeach;
                $this->getResponse()->setRedirect(
                    $this->_url->getUrl('checkout/onepage/success')
               );
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
        $this->getResponse()->setRedirect(
            $this->_url->getUrl($redirect)
       );
    }
}
