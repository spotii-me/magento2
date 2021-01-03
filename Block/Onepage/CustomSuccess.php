<?php
namespace Spotii\Spotiipay\Block\Onepage;

class CustomSuccess extends \Magento\Framework\View\Element\Template
{

     /**
     * @var \Spotii\Spotiipay\Helper\Data
     */
    protected $spotiiHelper;
    
    public function __construct(
        \Spotii\Spotiipay\Helper\Data $spotiiHelper
    )
    {
        $this->spotiiHelper = $spotiiHelper;
    }
    public function getCustomSuccess()
    {
        $this->spotiiHelper->logSpotiiActions("Custom success");
    }
} 