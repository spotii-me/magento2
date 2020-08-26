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
                $this->_checkoutSession->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
                // $this->spotiiHelper->logSpotiiActions("QUOTE ID FROM COMPLETE" . $quote->getId());
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

                $this->messageManager->addSuccess("Spotiipay Transaction Completed");
                $redirect = 'checkout/onepage/success';
                $this->spotiiHelper->logSpotiiActions($redirect);
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
        $this->spotiiHelper->logSpotiiActions("End complete : State ". $order->getState() ." status ".$order->getStatus());  
        $this->getResponse()->setRedirect(
            $this->_url->getUrl($redirect)
       );
    }
}
