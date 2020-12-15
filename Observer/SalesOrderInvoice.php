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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

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
     * @var invoiceRepositoryFactory
     */
    protected $invoiceRepositoryFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
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
        \Magento\Sales\Model\OrderFactory $orderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Model\Order\InvoiceRepositoryFactory $invoiceRepositoryFactory,
        FilterBuilder $filterBuilder

    ) {
        $this->invoiceRepositoryFactory = $invoiceRepositoryFactory;
        $this->spotiiPayModel = $spotiiPayModel;
        $this->spotiiHelper = $spotiiHelper;
        $this->orderFactory = $orderFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    public function execute($observer)
    {
        $this->spotiiHelper->logSpotiiActions('Start invoice');
        $orderId = $observer->getData('order_id');
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        $this->spotiiHelper->logSpotiiActions($orderId);
        $this->spotiiHelper->logSpotiiActions($order->getEntityId());
        $this->spotiiHelper->logSpotiiActions($order->getStatus());
        
        if (!$order) {
            return $this;
        }
        $this->spotiiHelper->logSpotiiActions('order exists');

        if ($order->getStatus() == "paymentauthorised") 
        {
 
            try {
                $this->spotiiHelper->logSpotiiActions('Start change state of invoice');
               
                /*$filter = $this->filterBuilder->setField('order_id')
                ->setValue('%'.$orderId.'%')
                ->setConditionType("eq")
                ->create();
                
                $searchCriteria->setFilterGroups([$filterOR]);  

                $invoice=$this->invoiceRepositoryFactory->get($orderId);*/
                $invoiceCollection = $order->getInvoiceCollection();
                foreach($invoiceCollection as $invoice):
                    //var_dump($invoice);
                    $invoiceId =  $invoice->getId();
                    $this->spotiiHelper->logSpotiiActions($invoiceId);
                    $invoiceIncrementId =  $invoice->getIncrementId();
                    $this->spotiiHelper->logSpotiiActions($invoiceIncrementId);
                endforeach;
                $this->spotiiHelper->logSpotiiActions('invoice returned');
              
            
            } catch (Exception $e) {
                $this->spotiiHelper->logSpotiiActions('Invoicer: Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: '.$e->getMessage(), false);
                $order->save();
            }                
        }
 
	return $this;        
    }
}