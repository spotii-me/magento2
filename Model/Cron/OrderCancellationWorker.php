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
use Magento\Sales\Model\Order\InvoiceRepository;

/**
 * Class Transaction
 * @package Spotii\Spotiipay\Model\Gateway
 */
class OrderCancellationWorker
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var SpotiiApiConfigInterface
     */
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

    protected $_orderCollectionFactory;

    protected $stockRegistry;

    protected $date;
    protected $registry;
    protected $invoiceRepository;
    protected $orderRepository;
    protected $orderManagement;
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
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $registry,
        InvoiceRepository $invoiceRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->orderFactory = $orderFactory;
        $this->spotiiHelper = $spotiiHelper;
        $this->config = $config;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->logger = $logger;
        $this->orderInterface = $orderInterface;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->date = $date;
        $this->registry = $registry;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;

    }

    /**
     * Send orders to Spotii
     */
    public function execute()
    {
       

        $this->spotiiHelper->logSpotiiActions("****Order clean up process start****");
        $today = date("Y-m-d H:i:s");
        $this->spotiiHelper->logSpotiiActions("Current date : $today");
        $yesterday = date("Y-m-d H:i:s", strtotime("-1 days"));
        $yesterday = date('Y-m-d H:i:s', strtotime($yesterday));

        $hourAgo = date("Y-m-d H:i:s", strtotime("-10 minutes"));
        $hourAgo = date('Y-m-d H:i:s', strtotime($hourAgo));

        $today = date('Y-m-d H:i:s', strtotime($today));

        try {
                $this->registry->register('isSecureArea', true);
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
            
            $this->spotiiHelper->logSpotiiActions("****Order clean up process end****");
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
        try{
        if ($ordersCollection) {
            foreach ($ordersCollection as $orderObj) {
                $orderIncrementId = $orderObj->getIncrementId();
                $order = $this->orderInterface->loadByIncrementId($orderIncrementId);
                $payment = $order->getPayment();
                $paymentMethod =$payment->getMethod();
                $created = $order->getCreatedAt();

                if($paymentMethod == self::PAYMENT_CODE && $hourAgo > $created){
                // fix start
                $invoices = $order->getInvoiceCollection();
                $invoiceCollection = $order->getInvoiceCollection();
                foreach($invoiceCollection as $invoice):
                    $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED);
                    $this->invoiceRepository->save($invoice);
                endforeach;
                $shipments = $order->getShipmentsCollection();
                if ($shipments)
                {
                    foreach($shipments as $shipment) {
                        $shipment->delete();
                        $this->spotiiHelper->logSpotiiActions("shippment");
                    }
                }
                $creditmemos = $order->getCreditmemosCollection();
                if ($creditmemos) {
                    foreach($creditmemos as $creditmemo) {
                        $creditmemo->delete();
                        $this->spotiiHelper->logSpotiiActions("creditmemo");
                    }
                }
                // fix end

                $this->orderManagement->cancel($order->getId());
                //$order->cancel();
                $this->spotiiHelper->logSpotiiActions('Order #'.$orderIncrementId.' created at '.$created.' was cleaned up');

            }else if($paymentMethod == self::PAYMENT_CODE){
                $this->spotiiHelper->logSpotiiActions('Order #'.$orderIncrementId.' created at '.$created.' was not cleaned up');
            }
        }
        }
    } catch (\Exception $e) {
        $this->spotiiHelper->logSpotiiActions("Error while cleaning up orders by Spotii " . $e->getMessage());
    }

    }


}