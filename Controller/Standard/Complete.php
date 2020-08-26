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

            // $quoteId = $this->_checkoutSession->getLastQuoteId();
            $orderId = $this->getRequest()->getParam("id");
            $reference = $this->getRequest()->getParam("magento_spotii_id");
            $order = $this->_orderFactory->create()->loadByIncrementId($orderId);
            $this->_spotiipayModel->capturePostSpotii($order->getPayment(), $order->getGrandTotal());
            $order->setState("processing")->setStatus("paymentauthorised");
            $order->save();

            if ($order) {
                
                $this->_spotiipayModel->createTransaction(
                    $order,
                    $reference,
                    \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE
                );
                // $quote->collectTotals()->save();          
                $this->spotiiHelper->logSpotiiActions("Created transaction with reference $reference");

                // send email
               try {
                    $this->_orderSender->send($order);
                } catch (\Exception $e) {
                   $this->_helper->debug("Transaction Email Sending Error: " . json_encode($e));
                }

                $this->_checkoutSession->setLastSuccessQuoteId($order->getQouteId());
                $this->_checkoutSession->setLastQuoteId($order->getQouteId());
                $this->_checkoutSession->setLastOrderId($order->getEntityId());

                $this->spotiiHelper->logSpotiiActions("QUOTE ID FROM COMPLETE " . $order->getQouteId()." ORDER ID FROM COMPLETE " . $order->getEntityId());

                $this->messageManager->addSuccess("Spotiipay Transaction Completed");
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
            $this->_url->getUrl('checkout/onepage/success')
       );
    }
}
