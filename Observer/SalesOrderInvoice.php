<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.com/)
 */

namespace Spotii\Spotiipay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface as Logger;
use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Spotii\Spotiipay\Helper\Data;
use Spotii\Spotiipay\Model\SpotiiPay;
use \Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
/**
 * Class MethodAvailabilityObserver
 * @package Spotii\Spotiipay\Observer
 */
class SalesOrderInvoice implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var spotiiPayModel
     */
    protected $spotiiPayModel;
    /**
     * @var spotiiHelper
     */
    protected $spotiiHelper;

     /**
     * Construct
     *
     * @param SpotiiPay $spotiiPayModel
     * @param Data $spotiiHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SpotiiPay $spotiiPayModel,
        Data $spotiiHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->spotiiPayModel = $spotiiPayModel;
        $this->spotiiHelper = $spotiiHelper;
        $this->orderFactory = $orderFactory;
    }

    public function execute($observer)
    {
        $orderId = $observer->getData('order_id');
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);

        if (!$order) {
            return $this;
        }
    
        if ($order->getStatus() == "paymentauthorised" && $order->getPayment()->getMethodInstance()->getCode() == SpotiiPay::PAYMENT_CODE) {
 
            try {
                $this->spotiiHelper->logSpotiiActions('invoice');
                if(!$order->canInvoice()) {
                    $order->addStatusHistoryComment('Invoice: Order cannot be invoiced.', false);
                    $order->save();  
                }
 
                //START Handle Invoice
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
 
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
 
                $invoice->getOrder()->setCustomerNoteNotify(true);          
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment('Invoiced', false);
 
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
 
                $transactionSave->save();
                //END Handle Invoice
 
                //START Handle Shipment
                $shipment = $order->prepareShipment();
                $shipment->register();
 
                $order->setIsInProcess(true);
                $order->addStatusHistoryComment('Shipped.', false);
 
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                //END Handle Shipment
            } catch (Exception $e) {
                $order->addStatusHistoryComment('Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: '.$e->getMessage(), false);
                $order->save();
            }                
        }
 
	return $this;        
    }
}