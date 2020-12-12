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
        $this->spotiiHelper->logSpotiiActions('Start invoice');
        $orderId = $observer->getData('order_id');
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        $this->spotiiHelper->logSpotiiActions($orderId);
        $this->spotiiHelper->logSpotiiActions($order->getEntityId());
        $this->spotiiHelper->logSpotiiActions($order->getStatus());
        //$this->spotiiHelper->logSpotiiActions($order->getPayment()->getMethodInstance()->getCode());
        
        if (!$order) {
            return $this;
        }
        $this->spotiiHelper->logSpotiiActions('order exists');

        if ($order->getStatus() == "paymentauthorised") 
        {
 
            try {
                $this->spotiiHelper->logSpotiiActions('Create invoice');
                
                if(!$order->canInvoice()) {
                    $this->spotiiHelper->logSpotiiActions('Invoice: Order cannot be invoiced.'); 
                }
                //$order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_INVOICE, true);
                if($order->canInvoice()) {
                $this->spotiiHelper->logSpotiiActions('Invoicing..'); 
                //START Handle Invoice
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
 
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
 
                $invoice->getOrder()->setCustomerNoteNotify(true);          
                $invoice->getOrder()->setIsInProcess(true);
                $this->spotiiHelper->logSpotiiActions('Invoiced');
 
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
 
                $transactionSave->save();
                //END Handle Invoice
 
                //START Handle Shipment
                $shipment = $order->prepareShipment();
                $shipment->register();
 
                $order->setIsInProcess(true);
                $this->spotiiHelper->logSpotiiActions('Shipped.', false);
 
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                //END Handle Shipment
            }
            } catch (Exception $e) {
                $this->spotiiHelper->logSpotiiActions('Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: '.$e->getMessage(), false);
                $order->save();
            }                
        }
 
	return $this;        
    }
}