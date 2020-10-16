/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)

define([
    'jquery',
    'ko',
    'uiComponent',
    'domReady!',
    'Magento_Catalog/js/product/storage/storage-service'
], function ($, ko, Component,storage) {
    'use strict';

    return /*Component.extend({
        initialize: function () {
            //initialize parent Component
            this._super();
            this.processSpotiiDocument();
          //  this.initIdsStorage();
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
            storage.onStorageInit(this.identifiersConfig.namespace, this.idsStorageHandler.bind(this));
            return this;
        },

        idsStorageHandler: function(idsStorage){
            this.productStorage = storage.createStorage(this.productStorageConfig);
            this.productStorage.data.subscribe(this.dataCollectionHandler.bind(this));
            console.log(this.productStorage);
        },
        dataCollectionHandler: function(data){
            console.log("data");
            console.log(data);
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
});*/
define([
  "underscore",
  "uiElement",
  "Magento_Catalog/js/product/storage/storage-service",
], function (_, Element, storage) {
  "use strict";
  return Element.extend({
    defaults: {
      identifiersConfig: { namespace: "recently_viewed_product" },
      productStorageConfig: {
        namespace: "product_data_storage",
        updateRequestConfig: { method: "GET", dataType: "json" },
        className: "DataStorage",
      },
    },
    initialize: function () {
      this._super().initIdsStorage().initDataStorage();
      return this;
    },
    initIdsStorage: function () {
      storage.onStorageInit(
        this.identifiersConfig.namespace,
        this.idsStorageHandler.bind(this)
      );
      console.log(storage)
      return this;
    },
    initDataStorage: function () {
      storage.onStorageInit(
        this.productStorageConfig.namespace,
        this.dataStorageHandler.bind(this)
      );
      return this;
    },
    dataStorageHandler: function (dataStorage) {
      this.productStorage = dataStorage;
      this.productStorage.add(this.data.items);
    },
    idsStorageHandler: function (idsStorage) {
      this.idsStorage = idsStorage;
      console.log(idsStorage)
      this.productStorage.data.subscribe(this.dataCollectionHandler.bind(this));
    },
    dataCollectionHandler: function(data){
        console.log("data");
        console.log(data);
    }
  });
});
