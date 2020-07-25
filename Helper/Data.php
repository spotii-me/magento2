<?php

namespace Spotii\Spotiipay\Helper;

use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Spotii\Spotiipay\Controller\AbstractController\SpotiiPay;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Model\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\QuoteManagement;
/**
 * Spotii Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SPOTII_LOG_FILE_PATH = '/var/log/spotiipay.log';

    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    /**
    * @var CheckoutSession
    */
   private $checkoutSession;
   protected $_quoteManagement;
    /**
    * Initialize dependencies.
    * 
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Magento\Catalog\Model\Product $product,
    * @param Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
    * @param Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
    * @param Magento\Customer\Model\CustomerFactory $customerFactory,
    * @param Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    * @param Magento\Sales\Model\Order $order    
    * @param SpotiiApiConfigInterface $spotiiApiConfig
    */ 
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order $order,
        \Magento\CatalogInventory\Observer\ItemsForReindex $itemsForReindex,
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate,
        CheckoutSession $checkoutSession

    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->order = $order;
        $this->itemsForReindex = $itemsForReindex;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->_productFactory = $productFactory;
        $this->_quoteManagement = $quoteManagement;
        $this->orderService = $orderService;
        $this->shippingRate = $shippingRate;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
 
    /**
     * Dump Spotii log actions
     *
     * @param string $msg
     * @return void
     */
    public function logSpotiiActions($data = null)
    {
        if ($this->spotiiApiConfig->isLogTrackerEnabled()) {
            $writer = new \Zend\Log\Writer\Stream(BP . self::SPOTII_LOG_FILE_PATH);
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($data);
        }
    }

    /**
     * block Order On Your Store
     * 
     * @param object $quote
     * @return object $order
     * @throws LocalizedException
     * 
    */
    public function setInventoryBefore($quote) {
       
        try{
        $this->logSpotiiActions("setInventoryBefore..");

            // $quote->setInventoryProcessed(true);
            $quote->collectTotals();
            $quote->save();
            $order = $this->_quoteManagement->submit($quote);
            $order->save();         
            
            // $increment_id = $order->getRealOrderId();
            $this->logSpotiiActions("setInventoryBefore done..");

        } catch(\Exception $e) {
                $this->logSpotiiActions($e->getMessage());
                throw new LocalizedException(__($e->getMessage()));
        }
        return $order;           
    }
}
