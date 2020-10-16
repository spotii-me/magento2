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

/**
 * Class Cart
 * @package Spotii\Spotiipay\Block\SpotiiWidget
 */
class Cart extends Template
{
    const MIN_PRICE = 0;
    const MAX_PRICE = 100000;
    const WIDGET_TYPE = "cart";
    protected $spotiiHelper;
    /**
     * @var CartWidgetConfigInterface
     */
    private $cartWidgetConfig;
    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    protected $cart;
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
        \Spotii\Spotiipay\Helper\Data $spotiiHelper,
        array $data
    ) {
        $this->cartWidgetConfig = $cartWidgetConfig;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->spotiiHelper = $spotiiHelper;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    /**
     * Get JS Config
     *
     * @return array
     */
    public function getJsConfig()
    {
        $this->spotiiHelper->logSpotiiActions("cart");
        $showWidget = true;

        foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
            $this->spotiiHelper->logSpotiiActions($item->getId());
            $isAvaliableOnSpotii=$item->getAttributeText('spotii_product');
            $this->spotiiHelper->logSpotiiActions($isAvaliableOnSpotii);
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
