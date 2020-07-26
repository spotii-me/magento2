<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

 namespace Spotii\Spotiipay\Plugin\Sales\Model\Order\Payment;

 use Magento\Sales\Model\Order\Payment;


 class SpotiiPayment extends Payment {
	
	 protected $_canRefund = true;
	 
    	 /**
     	 * @var \Spotii\Spotiipay\Helper\Data
     	 */
	 protected $spotiiHelper;
	
	 public function __construct(\Spotii\Spotiipay\Helper\Data $spotiiHelper) {
	 	$this->_spotiiHelper = $spotiiHelper;
		parent::__construct();
	 }

	 public function canRefund() {
	 	return true;
	 }

	 public function canRefundPartialPerInvoice() {
	        return true;
	 }
    	   public function refund($creditmemo)
    	  {
        	$this->spotiiHelper->logSpotiiActions("****Refund Start****");
          } 		
 }
