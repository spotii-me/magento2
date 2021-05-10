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
		initialize: async function () {
			//initialize parent Component
			this._super();
			await this.loopThroughProduct();
			this.processSpotiiDocument();
		},

		loopThroughProduct: function () {
			for (let product of allProducts) {
				console.log(this.jsConfig, 'js config in loop through product')
				this.jsConfig.renderToPath = ['#' + product.id];
				console.log(this.jsConfig.renderToPath, 'render Path in js config');
				console.log(product.id, 'product ID tag');
			}
		},

		processSpotiiDocument: function () {
			console.log('rendering started');
			var self = this;
			console.log(self.jsConfig);
			// const allProducts = document.getElementsByClassName('price-wrapper');
			// console.log(allProducts, 'allProducts');
			// for (let product of allProducts) {
			// 	self.jsConfig.renderToPath = ['#' + product.id];
			// 	console.log(self.jsConfig.renderToPath, 'render Path in js config');
			// 	document.spotiiConfig = self.jsConfig;
			// 	console.log(product.id, 'product ID tag');
			// 	console.log(
			// 		document.spotiiConfig.renderToPath,
			// 		'render Path in spotii config'
			// 	);
			// 	console.log(document.spotiiConfig, 'spotiiConfig');

			// 	if (!document.spotiiConfig) {
			// 		console.warn(
			// 			'SpotiiPay: document.spotiiConfig is not set, cannot render widget'
			// 		);
			// 		return;
			// 	}

			// 	var script = document.createElement('script');
			// 	script.type = 'text/javascript';
			// 	script.src =
			// 		'https://widget.spotii.me/v1/javascript/price-widget?uuid=' +
			// 		document.spotiiConfig.merchantID;
			// 	$('head').append(script);

			// 	console.log('dom loaded');
			// }
			document.spotiiConfig = self.jsConfig;
			console.log(document.spotiiConfig.renderToPath,'render Path in spotii config');
			console.log(document.spotiiConfig, 'spotiiConfig');

			if (!document.spotiiConfig) {
				console.warn(
					'SpotiiPay: document.spotiiConfig is not set, cannot render widget'
				);
				return;
			}

			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src =
				'https://widget.spotii.me/v1/javascript/price-widget?uuid=' +
				document.spotiiConfig.merchantID;
			$('head').append(script);
			console.log('dom loaded');
		},
	});
});
