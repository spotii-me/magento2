<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Block\SpotiiWidget;

use Magento\Framework\View\Element\Template;
use Spotii\Spotiipay\Model\Config\Container\CartWidgetConfigInterface;
use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;
use Spotii\Spotiipay\Helper\Data as SpotiiHelper;

/**
 * Class Cart
 * @package Spotii\Spotiipay\Block\SpotiiWidget
 */
class Cart extends Template
{
    const MIN_PRICE = 0;
    const MAX_PRICE = 100000;
    const WIDGET_TYPE = "cart";
    /**
     * @var CartWidgetConfigInterface
     */
    private $cartWidgetConfig;
    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    protected $cart;
    private $productRepository; 
        /**
     * @var SpotiiHelper
     */
    protected $spotiiHelper;
    /**
     * ProductWidget constructor.
     *
     * @param Template\Context $context
     * @param CartWidgetConfigInterface $cartWidgetConfig
     * @param SpotiiApiConfigInterface $spotiiApiConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CartWidgetConfigInterface $cartWidgetConfig,
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        SpotiiHelper $spotiiHelper,
        array $data
    ) {
        $this->cartWidgetConfig = $cartWidgetConfig;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->cart = $cart;
        $this->spotiiHelper = $spotiiHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get JS Config
     *
     * @return array
     */
    public function getJsConfig()
    {
        $showWidget = true;
        $grandTotal = floatval($this->cart->getQuote()->getGrandTotal());
        $limit = floatval($this->spotiiApiConfig->getAvailabilityAmount());
        $this->spotiiHelper->logSpotiiActions($grandTotal);
        $this->spotiiHelper->logSpotiiActions($limit);
        if($grandTotal> $limit){
            $showWidget = false; 
            $this->spotiiHelper->logSpotiiActions("False");
        }
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
            $product= $this->productRepository->get($item->getSku());
            $isAvaliableOnSpotii=$product->getAttributeText('spotii_product');
            if($isAvaliableOnSpotii=="No"){
                $showWidget = false;
            }
        }
        if($showWidget){
        $result = [
            'targetXPath' => $this->cartWidgetConfig->getTargetXPath(),
            'renderToPath' => $this->cartWidgetConfig->getRenderToPath(),
            'forcedShow' => $this->cartWidgetConfig->getForcedShow(),
            'alignment' => $this->cartWidgetConfig->getAlignment(),
            'merchantID' => $this->spotiiApiConfig->getMerchantId(),
            'theme' => $this->cartWidgetConfig->getTheme(),
            'widthType' => $this->cartWidgetConfig->getWidthType(),
            'widgetType' => self::WIDGET_TYPE,
            'minPrice' => self::MIN_PRICE,
            'maxPrice' => self::MAX_PRICE,
            'imageUrl' => $this->cartWidgetConfig->getImageUrl(),
            'hideClasses' => $this->cartWidgetConfig->getHideClass()
        ];

        foreach ($result as $key => $value) {
            if (is_null($result[$key]) || $result[$key] == '') {
                unset($result[$key]);
            }
        }
        return $result;
    }else
    return [];
    }
}
 
