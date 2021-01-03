<?php
namespace Spotii\Spotiipay\Block\Onepage;

class CustomSuccess extends \Magento\Checkout\Block\Onepage\Success
{

     /**
     * @var \Spotii\Spotiipay\Helper\Data
     */
    protected $spotiiHelper;

    protected $orderItemsDetails;
    protected $_checkoutSession;
    
    public function __construct(
        \Spotii\Spotiipay\Helper\Data $spotiiHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\Order $orderItemsDetails,
        array $data = []
    )
    {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->orderItemsDetails = $orderItemsDetails;
        $this->spotiiHelper = $spotiiHelper;
    }
    public function getOrderItemsDetails()
    {
        $this->spotiiHelper->logSpotiiActions("Custom success");
        $IncrementId  = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
        $order = $this->orderItemsDetails->loadByIncrementId($IncrementId);
       return $order;
    }
} 
