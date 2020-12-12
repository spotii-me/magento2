<?php
 namespace Spotii\Spotiipay\Sales\Model;
 
 use Magento\Framework\App\ResourceConnection;
 use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
 use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
 use Magento\Sales\Api\InvoiceOrderInterface;
 use Magento\Sales\Api\OrderRepositoryInterface;
 use Magento\Sales\Model\Order\Config as OrderConfig;
 use Magento\Sales\Model\Order\Invoice\NotifierInterface;
 use Magento\Sales\Model\Order\InvoiceDocumentFactory;
 use Magento\Sales\Model\Order\InvoiceRepository;
 use Magento\Sales\Model\Order\OrderStateResolverInterface;
 use Magento\Sales\Model\Order\PaymentAdapterInterface;
 use Magento\Sales\Model\Order\Validation\InvoiceOrderInterface as InvoiceOrderValidator;
 use Psr\Log\LoggerInterface;
 use Spotii\Spotiipay\Helper\Data;

 class InvoiceOrder implements InvoiceOrderInterface
 {
     private $resourceConnection;
 
     private $orderRepository;
 
     private $invoiceDocumentFactory;
 
     private $paymentAdapter;
 
     private $orderStateResolver;
 
     private $config;
 
     private $invoiceRepository;
 
     private $invoiceOrderValidator;
 
     private $notifierInterface;
 
     private $logger;
    /**
     * @var spotiiHelper
     */
    protected $spotiiHelper;

     public function __construct(
         ResourceConnection $resourceConnection,
         OrderRepositoryInterface $orderRepository,
         InvoiceDocumentFactory $invoiceDocumentFactory,
         PaymentAdapterInterface $paymentAdapter,
         OrderStateResolverInterface $orderStateResolver,
         OrderConfig $config,
         InvoiceRepository $invoiceRepository,
         InvoiceOrderValidator $invoiceOrderValidator,
         NotifierInterface $notifierInterface,
         Data $spotiiHelper,
         LoggerInterface $logger 
     ) {
         $this->resourceConnection = $resourceConnection;
         $this->orderRepository = $orderRepository;
         $this->invoiceDocumentFactory = $invoiceDocumentFactory;
         $this->paymentAdapter = $paymentAdapter;
         $this->orderStateResolver = $orderStateResolver;
         $this->config = $config;
         $this->invoiceRepository = $invoiceRepository;
         $this->invoiceOrderValidator = $invoiceOrderValidator;
         $this->notifierInterface = $notifierInterface;
         $this->logger = $logger;
         $this->spotiiHelper = $spotiiHelper;
     }
 
     public function execute(
         $orderId,
         $capture = false,
         array $items = [],
         $notify = false,
         $appendComment = false,
         InvoiceCommentCreationInterface $comment = null,
         InvoiceCreationArgumentsInterface $arguments = null
     ) {
        $this->spotiiHelper->logSpotiiActions('InvoiceOrder');
         $connection = $this->resourceConnection->getConnection('sales');
         $order = $this->orderRepository->get($orderId);
         $invoice = $this->invoiceDocumentFactory->create(
             $order,
             $items,
             $comment,
             ($appendComment && $notify),
             $arguments
         );
         $errorMessages = $this->invoiceOrderValidator->validate(
             $order,
             $invoice,
             $capture,
             $items,
             $notify,
             $appendComment,
             $comment,
             $arguments
         );
         if ($errorMessages->hasMessages()) {
             throw new \Magento\Sales\Exception\DocumentValidationException(
                 __("Invoice Document Validation Error(s):\n" . implode("\n", $errorMessages->getMessages()))
             );
         }
         $connection->beginTransaction();
         try {
             $order = $this->paymentAdapter->pay($order, $invoice, $capture);
             $order->setState(
                 $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
             );
             $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
             $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
             $this->invoiceRepository->save($invoice);
             $this->orderRepository->save($order);
             $connection->commit();
         } catch (\Exception $e) {
             $this->logger->critical($e);
             $connection->rollBack();
             throw new \Magento\Sales\Exception\CouldNotInvoiceException(
                 __('Could not save an invoice, see error log for details')
             );
         }
         if ($notify) {
             if (!$appendComment) {
                 $comment = null;
             }
             $this->notifierInterface->notify($order, $invoice, $comment);
         }
         return $invoice->getEntityId();
     }
 }