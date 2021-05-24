<?php

namespace Spotii\Spotiipay\Block;

class Success extends \Magento\Checkout\Block\Onepage\Success {

    public function getOrder() {
        return $this->_checkoutSession->getLastRealOrder();
    }

}
