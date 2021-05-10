/*
 * @category    Spotii
 * @package     Spotii_Spotiipay
 * @copyright   Copyright (c) Spotii (https://www.spotii.me/)
 */
define(['jquery', 'ko', 'uiComponent', 'domReady!'], function (
	$,
	ko,
	Component
) {
	'use strict';

	return Component.extend({
		initialize: function () {
			//initialize parent Component
			this._super();
			this.processSpotiiDocument();
		},

		processSpotiiDocument: function () {
			console.log('rendering started');
			var self = this;
			console.log(self.jsConfig);
			const allProducts = document.getElementsByClassName('product-item-info');
			console.log(allProducts, 'allProducts')
			for (let product of allProducts) {
				const text = document.createElement('span');
				text.appendChild(document.createTextNode('Offered on Spotii'))
				product.appendChild(text);

				// self.jsConfig.renderToPath = ["#"+product.id];
				// console.log(self.jsConfig.renderToPath , 'render Path in js config')

				// document.spotiiConfig = self.jsConfig;


				// console.log(product.id, 'product ID tag');
				// console.log(document.spotiiConfig.renderToPath, 'render Path in spotii')
				// console.log(document.spotiiConfig, 'spotiiConfig')

				// if (!document.spotiiConfig) {
				// 	console.warn(
				// 		'SpotiiPay: document.spotiiConfig is not set, cannot render widget'
				// 	);
				// 	return;
				// }

				// var script = document.createElement('script');
				// script.type = 'text/javascript';
				// script.src =
				// 	'https://widget.spotii.me/v1/javascript/price-widget?uuid=' +
				// 	document.spotiiConfig.merchantID;
				// $('head').append(script);

				console.log('dom loaded');
			}
		}
	});
});
