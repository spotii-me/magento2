<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Model\Config\Source\Order;

/**
 * Class Mode
 * @package Spotii\Spotiipay\Model\Config\Source\Payment
 */
class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    public function __construct(Template\Context $context,
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
        ) 
    {       
        $this->statusCollectionFactory = $statusCollectionFactory;      
        parent::__construct($context);
    }


    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();        
        return $options;
    }
}
