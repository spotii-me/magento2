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

    protected $date;

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
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
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
        $this->date = $date;
    }

    /**
     * Send orders to Spotii
     */
    public function execute()
    {
        $this->spotiiHelper->logSpotiiActions("****Inventory clean up process start****");
        $today = date("Y-m-d H:i:s");
        $this->spotiiHelper->logSpotiiActions("Current date : $today");
        $yesterday = date("Y-m-d H:i:s", strtotime("-1 days -1 hours"));
        $yesterday = date('Y-m-d H:i:s', strtotime($yesterday));

        $hourAgo = date("Y-m-d H:i:s", strtotime("-1 hours"));
        $hourAgo = date('Y-m-d H:i:s', strtotime($hourAgo));

        $today = date('Y-m-d H:i:s', strtotime($today));

        try {
                $ordersCollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter(
                'status',
                ['eq' => 'pending']
                )->addFieldToFilter(
                 'created_at',
                ['gteq' => $yesterday]
                )->addFieldToFilter(
                'created_at',
                ['lteq' => $today]
                )->addAttributeToSelect('increment_id');
                

                $this->spotiiHelper->logSpotiiActions("ordersCollection ".sizeof($ordersCollection));
 
                $this->cleanOrders($ordersCollection, $hourAgo);
            
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
    private function cleanOrders($ordersCollection = null, $hourAgo)
    {
        if ($ordersCollection) {
            foreach ($ordersCollection as $orderObj) {
                $orderIncrementId = $orderObj->getIncrementId();
                $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
                $payment = $order->getPayment();
                $paymentMethod =$payment->getMethod();
                $created = $order->getCreatedAt();

                //Convert to store timezone
                $created = $this->date(new \DateTime($created));

                //To print or display this you can use following.
                //Feel free to tweak the format
                $dateAsString = $created->format('Y-m-d H:i:s');

                if($paymentMethod == self::PAYMENT_CODE && $hourAgo > $dateAsString){
                    $this->spotiiHelper->logSpotiiActions("Order cleaned up ".$orderIncrementId.' '.$created);
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
                    $order->setState('closed')->setStatus('closed');
                    $order->save();
                    
            }else if($paymentMethod == self::PAYMENT_CODE){
                $this->spotiiHelper->logSpotiiActions("Order not cleaned up ".$orderIncrementId.' '.$created);
            }
        }
        }
    }
}
