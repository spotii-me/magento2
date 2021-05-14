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
			this.loadSpotiiScript();
			this.spotiiCatalogWidget();
			// this.processSpotiiDocument();
		},

		spotiiCatalogWidget: function(){
			var self = this;
			console.log(self, 'self')
			let classToRender = self.jsConfig.renderToPath
			console.log(classToRender, 'class To render')
			const allProducts = document.getElementsByClassName(classToRender);
			console.log(allProducts, 'all products')
			for(let product of allProducts){
				console.log(product, 'product')
				window.loadSpotiiWidget(window, document, self.jsConfig.targetXPath, product.id, self.jsConfig.currency)
			}

		},

		loadSpotiiScript: function(){
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = 'https://widget.spotii.me/v1/javascript/vperfumes-price-widget.js';
			$("head").append(script);
			console.log("dom loaded");
		},

		processSpotiiDocument: function () {
		var isNumeric = function(source) {
				return !isNaN(parseFloat(source)) && isFinite(source)
		};
		var isAlphabet = function(e) {
				return /^[a-zA-Z()]+$/.test(e)
		};
		var parsePrice = function(source) {
				var priceStr = "",
						i = 0;
				for (; i < source.length; i += 1) {
						if (isNumeric(source[i]) || source[i] === ".") {
								if (i > 0 && source[i] === "." && isAlphabet(source[i - 1])) {
										continue
								}
								priceStr += source[i]
						}
				}
				return parseFloat(priceStr)
		};
			const logo = `<svg viewBox="0 0 575 156" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
			style="max-height: 100%; vertical-align: text-bottom; height: 0.90em"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="nonzero">
					<path d="M93.4,42.68 L116.74,19.34 C102.292199,4.90544225 81.9818341,-2.03751498 61.7212349,0.532217389 C41.4606357,3.10194976 23.5267068,14.8955829 13.14,32.48 C39.0890855,17.1797853 72.1029078,21.3754119 93.4,42.68 Z"
			fill="#FFC4BE"></path>
			<path d="M 23.33 112.75 L 0 136.08 C 14.4514 150.507 34.7594 157.445 55.0172 154.875 C 75.2749 152.306 93.2079 140.518 103.6 122.94 C 77.6485 138.237 44.6361 134.046 23.33 112.75 Z"
			fill="#FF4C44"></path>
			<path d="M93.4,42.68 L23.33,112.75 C44.6360832,134.04624 77.648524,138.237056 103.6,122.94 C118.900215,96.9909145 114.704588,63.9770922 93.4,42.68 Z"
						fill="#FFC4BE"></path>
			<path d="M23.33,112.75 L93.4,42.68 C72.1029078,21.3754119 39.0890855,17.1797853 13.14,32.48 C-2.1570557,58.431476 2.03375993,91.4439168 23.33,112.75 Z"
			fill="#FF4C44"></path>
			<path d="M228,94.14 C228,108.71 216.85,122.94 194,122.94 C167.25,122.94 158.68,105.63 158,96.2 L180.12,92.2 C180.47,98.03 184.58,103.69 193.49,103.69 C200.18,103.69 203.44,100.09 203.44,96.32 C203.44,93.23 201.38,90.66 195.04,89.32 L185.27,87.09 C167.09,83.15 159.89,72.86 159.89,60.86 C159.89,45.26 173.61,32.57 192.64,32.57 C217.33,32.59 225.9,48 226.76,58 L205.15,61.95 C204.47,56.29 200.87,51.49 192.98,51.49 C187.98,51.49 183.72,54.4 183.72,58.86 C183.72,62.46 186.64,64.52 190.41,65.2 L201.72,67.43 C219.39,71 228,81.62 228,94.14 Z M425.84,77.72 C425.84,102.699839 405.589839,122.95 380.61,122.95 C355.630161,122.95 335.38,102.699839 335.38,77.72 C335.38,52.7401608 355.630161,32.49 380.61,32.49 C392.606555,32.4873468 404.112524,37.2517846 412.59537,45.7346302 C421.078215,54.2174759 425.842653,65.723445 425.84,77.72 Z M399.84,77.72 C399.84,65.99 391.24,56.49 380.64,56.49 C370.04,56.49 361.4,66 361.4,77.72 C361.4,89.44 370,98.94 380.61,98.94 C391.22,98.94 399.81,89.44 399.81,77.72 L399.84,77.72 Z M518.92,0 C511.740298,-4.39629938e-16 505.92,5.82029825 505.92,13 C505.92,20.1797017 511.740298,26 518.92,26 C526.099702,26 531.92,20.1797017 531.92,13 C531.92,9.55218563 530.550361,6.24558476 528.112388,3.80761184 C525.674415,1.36963892 522.367814,2.11117741e-16 518.92,0 Z M505.92,122.94 L532,122.94 L532,46.8 L505.89,32.48 L505.92,122.94 Z M561.94,49.7 C569.119702,49.7 574.94,43.8797017 574.94,36.7 C574.94,29.5202983 569.119702,23.7 561.94,23.7 C554.760298,23.7 548.94,29.5202983 548.94,36.7 C548.94,43.8797017 554.760298,49.7 561.94,49.7 Z M548.94,56.13 L548.94,122.94 L575,122.94 L575,70.45 L548.94,56.13 Z M447.18,32.48 L431.49,32.48 L431.49,58.64 L447.18,58.64 L447.18,94.21 C447.18,101.831403 450.208274,109.140506 455.598357,114.528714 C460.988441,119.916922 468.298598,122.942653 475.92,122.94 L488.92,122.940002 L488.92,96.88 L485.43,96.88 C478.69,96.88 473.23,90.88 473.23,83.4 L473.23,58.64 L488.92,58.64 L488.92,32.48 L473.24,32.48 L473.24,14.53 L447.1,0 L447.18,32.48 Z M265.33,115.93 L265.33,155.42 L239.26,141.1 L239.26,32.48 L265.33,32.48 L265.33,39.48 C271.920131,34.9262107 279.739582,32.4848123 287.75,32.48 C310.93,32.48 329.75,52.73 329.75,77.71 C329.75,102.69 310.96,122.94 287.75,122.94 C279.739292,122.927202 271.921215,120.482745 265.33,115.93 Z M265.33,78.45 C265.69,89.83 274.12,98.93 284.49,98.93 C295.1,98.93 303.7,89.43 303.7,77.71 C303.7,65.99 295.1,56.48 284.49,56.48 C274.12,56.48 265.69,65.59 265.33,76.96 L265.33,78.45 Z"
						fill="#FF4C44"></path></g></svg>`;

			var self = this;
			console.log(self.jsConfig);
			const allProducts = document.getElementsByClassName('price-box price-final_price');
			console.log(allProducts, 'allProducts')
			for (let product of allProducts) {
				const price = parsePrice(product.innerText);
				const priceText = this.jsConfig.currency;
				const text = document.createElement('span');
				text.appendChild(document.createTextNode('or 4 free interest free payments of ' + priceText + " " + (price/4).toFixed(2) + ' with '))
				const spotiiLogo = document.createElement('span')
				spotiiLogo.innerHTML = logo;
				product.appendChild(document.createElement("br"));
				product.appendChild(text);
				product.appendChild(spotiiLogo);
			}
		}
	});
});
