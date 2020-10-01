<?php

namespace Spotii\Spotiipay\Helper;

use Magento\Framework\App\Action\Action;

/**
 * Spotii Helper
 */
class CheckInventory extends Action
{

    /**
     * @var SpotiiApiConfigInterface
     */
    protected $stockRegistry;
    protected $_jsonHelper;
    protected $_resultJsonFactory;
    protected $spotiiHelper;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SpotiiApiConfigInterface $spotiiApiConfig
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Spotii\Spotiipay\Helper\Data $spotiiHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->spotiiHelper = $spotiiHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Dump Spotii log actions
     *
     * @param string $msg
     * @return void
     */
    public function execute()
    {
        $items = $_POST['items'];
        $this->spotiiHelper->logSpotiiActions($items);
        $flag = true;
        foreach ($items as $item) {
            $this->spotiiHelper->logSpotiiActions("checking ... " . $item->getSku());
    
            $sku = $item->getSku();
            $qtyOrdered = $item->getQtyOrdered();
    
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $qtyInStock= $stockItem->getQty();
            if($qtyInStock < $qtyOrdered){
               $flag = false ;            
            }
            }
            $json = $this->_jsonHelper->jsonEncode(["isInStock" => $flag]);
            $jsonResult = $this->_resultJsonFactory->create();
            $jsonResult->setData($json);
            return $jsonResult;
    }
}
