<?php
/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */

namespace Spotii\Spotiipay\Block\SpotiiWidget;

use Magento\Framework\View\Element\Template;
use Spotii\Spotiipay\Model\Config\Container\ProductWidgetConfigInterface;
use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;

/**
 * Class ProductView
 * @package Spotii\Spotiipay\Block\SpotiiWidget
 */
class ProductView extends Template
{
    const MIN_PRICE = 0;
    const MAX_PRICE = 100000;
    const WIDGET_TYPE = "product_page";

    /**
     * @var ProductWidgetConfigInterface
     */
    private $productWidgetConfig;
    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    protected $_registry;
    /**
     * ProductWidget constructor.
     *
     * @param Template\Context $context
     * @param ProductWidgetConfigInterface $productWidgetConfig
     * @param SpotiiApiConfigInterface $spotiiApiConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductWidgetConfigInterface $productWidgetConfig,
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Magento\Framework\Registry $registry,
        array $data
    ) {
        $this->productWidgetConfig = $productWidgetConfig;
        $this->spotiiApiConfig = $spotiiApiConfig;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get JS Config
     *
     * @return array
     */
    public function getJsConfig()
    {
        $product = $this->_registry->registry('current_product');
        $isAvaliableOnSpotii=$product->getAttributeText('spotii_product');
        if($isAvaliableOnSpotii=="Yes"){
        $result = [
            'targetXPath' => $this->productWidgetConfig->getTargetXPath(),
            'renderToPath' => $this->productWidgetConfig->getRenderToPath(),
            'forcedShow' => $this->productWidgetConfig->getForcedShow(),
            'alignment' => $this->productWidgetConfig->getAlignment(),
            'merchantID' => $this->spotiiApiConfig->getMerchantId(),
            'theme' => $this->productWidgetConfig->getTheme(),
            'widthType' => $this->productWidgetConfig->getWidthType(),
            'widgetType' => self::WIDGET_TYPE,
            'minPrice' => self::MIN_PRICE,
            'maxPrice' => self::MAX_PRICE,
            'imageUrl' => $this->productWidgetConfig->getImageUrl(),
            'hideClasses' => $this->productWidgetConfig->getHideClass()
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
