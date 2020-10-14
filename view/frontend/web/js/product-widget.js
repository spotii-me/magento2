/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'domReady!',
    'Magento_Catalog/js/product/storage/storage-service',
    'underscore',
    'mageUtils',
    'mage/translate',
    'Magento_Catalog/js/product/storage/ids-storage',
    'Magento_Catalog/js/product/storage/data-storage',
    'Magento_Catalog/js/product/storage/ids-storage-compare'
], function ($, ko, Component,storage, _, utils, $t, IdsStorage, DataStore, IdsStorageCompare) {
    'use strict';

    return Component.extend({
        initialize: function () {
            //initialize parent Component
            this._super();
            this.processSpotiiDocument();
            this.initIdsStorage();
        },
        
        identifiersConfig: {
            namespace: 'product_data_storage'
        },

        productStorageConfig: {
            namespace: 'product_data_storage',
            customerDataProvider: 'product_data_storage',
            className: 'DataStorage'
        },

        initIdsStorage: function(){
            console.log(storage.getStorage(this.identifiersConfig.namespace));
            storage.onStorageInit(this.identifiersConfig.namespace, this.idsStorageHandler.bind(this));
            return this;
        },

        idsStorageHandler: function(idsStorage){
            this.productStorage = storage.createStorage(this.productStorageConfig);
            console.log(this.productStorage);
        },
        processSpotiiDocument: function() {
            console.log("rendering started");
            var self = this;
            console.log(self.jsConfig);
            document.spotiiConfig = self.jsConfig;

            if (!document.spotiiConfig) {
                console.warn('SpotiiPay: document.spotiiConfig is not set, cannot render widget');
                return;
            }

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://widget.spotii.me/v1/javascript/price-widget?uuid=' + document.spotiiConfig.merchantID;
            $("head").append(script);

            console.log("dom loaded");
        }
    });
});
