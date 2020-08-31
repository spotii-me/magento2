<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Cron;

use Spotii\Spotiipay\Helper\Data as SpotiiHelper;
use Spotii\Spotiipay\Model\Api\ConfigInterface;
use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;

/**
 * Class Transaction
 * @package Spotii\Spotiipay\Model\Gateway
 */
class InventoryWorker
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    /**
     * @var \Spotii\Spotiipay\Model\Api\ProcessorInterface
     */
    private $spotiiApiProcessor;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $orderInterface;
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SpotiiHelper
     */
    private $spotiiHelper;

    /**
     * @var SpotiiApiConfigInterface
     */
    protected $spotiiApiIdentity;

    protected $statusCollectionFactory;

    protected $_orderCollectionFactory;

    protected $stockRegistry;

    const PAYMENT_CODE = 'spotiipay';
    /**
     * Transaction constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param SpotiiHelper $spotiiHelper
     * @param ConfigInterface $config
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Spotii\Spotiipay\Model\Api\ProcessorInterface $spotiiApiProcessor
     * @param SpotiiApiConfigInterface $spotiiApiConfig
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        SpotiiHelper $spotiiHelper,
        ConfigInterface $config,
        \Psr\Log\LoggerInterface $logger,
        \Spotii\Spotiipay\Model\Api\ProcessorInterface $spotiiApiProcessor,
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->orderFactory = $orderFactory;
        $this->spotiiHelper = $spotiiHelper;
        $this->config = $config;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->spotiiApiProcessor = $spotiiApiProcessor;
        $this->logger = $logger;
        $this->orderInterface = $orderInterface;
        $this->statusCollectionFactory=$statusCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Send orders to Spotii
     */
    public function execute()
    {
        $this->spotiiHelper->logSpotiiActions("****Inventory clean up process start****");
        $today = date("Y-m-d H:i:s");
        $this->spotiiHelper->logSpotiiActions("Current date : $today");
        $yesterday = date("Y-m-d H:i:s", strtotime("-1 days"));
        $yesterday = date('Y-m-d H:i:s', strtotime($yesterday));
        $today = date('Y-m-d H:i:s', strtotime($today));
        $status = $this->spotiiApiConfig->getNewOrderStatus();
        $this->spotiiHelper->logSpotiiActions("cron ".$status." type ".gettype($status));
        try {
                $ordersCollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter(
                'status',
                ['eq' => $status]
                )->addFieldToFilter(
                 'created_at',
                ['gteq' => $yesterday]
                )->addFieldToFilter(
                'created_at',
                ['lteq' => $today]
                )->addAttributeToSelect('increment_id');
                

                $this->spotiiHelper->logSpotiiActions("ordersCollection ".sizeof($ordersCollection));
 
                $this->cleanOrders($ordersCollection);
            
            $this->spotiiHelper->logSpotiiActions("****Inventory clean up process end****");
        } catch (\Exception $e) {
            $this->spotiiHelper->logSpotiiActions("Error while cleaning up orders by Spotii" . $e->getMessage());
        }
    }

    /**
     * Build Payload
     *
     * @param null $ordersCollection
     * @return array
     */
    private function cleanOrders($ordersCollection = null)
    {
        if ($ordersCollection) {
            foreach ($ordersCollection as $orderObj) {
                $orderIncrementId = $orderObj->getIncrementId();
                $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
                $payment = $order->getPayment();
                $paymentMethod =$payment->getMethod();
                $this->spotiiHelper->logSpotiiActions("Orders ".$orderIncrementId);
                if($paymentMethod == self::PAYMENT_CODE){
                
                    foreach ($order->getAllVisibleItems() as $item) {
                        $sku = $item->getSku();
                        $qtyOrdered = $item->getQtyOrdered();
                
                        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                
                        $qtyInStock= $stockItem->getQty();
                        $finalQty = $qtyInStock +$qtyOrdered;
                
                        $stockItem->setQty($finalQty);
                        $stockItem->setIsInStock((bool)$finalQty);
                        $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                    }
            }
        }
        }
    }
}
