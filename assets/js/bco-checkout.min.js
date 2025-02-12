/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/assets/js/bco-checkout.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/assets/js/bco-checkout.js":
/*!***************************************!*\
  !*** ./src/assets/js/bco-checkout.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

jQuery(function($) {
	const bco_wc = {

		update: function() {
			/**
			 * Refresh checkout in iframe
			 * In this example the name of iframe that contain Qvickly Checkout have the name checkout_iframe
			 */
			checkout_iframe.postMessage('update', '*');
		},

		lock: function() {
			/**
			 * Lock Qvickly checkout from customer interactions
			 * Will display loading animation and is used when the store is working on the order, for example when cart is updated
			 */
			checkout_iframe.postMessage('lock', '*');
		},

		unlock: function() {
			/**
			 * If checkout is locked, remove loading animation and customer can continue to interact with Qvickly Checkout
			 * Is used when store is done with work that affect the order and Qvickly Checkout, for example when cart is updated
			 */
			checkout_iframe.postMessage('unlock', '*');
		},

		purchase_complete: function() {
			/**
			 * Post back a reply to the purchase_initialized JS callback. 
			 * WooCommerce order was successfully created, now let Qvickly complete the purchase.
			 * The name of iframe that contain Qvickly Checkout have the name checkout_iframe
			 */
			console.log('purchase_complete');
			checkout_iframe.postMessage('purchase_complete', '*');
		},
		hide_overlay: function() {
			/**
			 * Enable Woocommerce order review when overlay is closed in BCO.
			 */
			console.log('hide_overlay');
			$('#bco-wrapper').removeClass( 'processing' ).unblock();
		},

		handleEvent: function(event) {
			if(event.origin != "") {
				try {
					var json = JSON.parse(event.data);
				} catch (e) {
					return;
				}

				switch (json.event) {
					case 'purchase_initialized':
						/**
						 * When an end user clicks on the "Purchase button".
						 */
						console.log('billmate_purchase_initialized');
						bco_wc.logToFile( 'purchase_initialized from Qvickly triggered' );
						bco_wc.getBillmateCheckout();

						$( 'body' ).on( 'bco_order_validation', function( event, bool ) {							
							if ( true === bool ) {
								// Success.
								bco_wc.purchase_complete();
							} else {
								// Fail.
							}
						});
						break;
					case 'address_selected':
						if ( 'checkout' !== bco_wc_params.checkout_flow ) {
							return;
						}

						// Don't update customer data in Woo if disabled via filter.
						if( 'no' === bco_wc_params.populate_address_fields ) {
							return;
						}
						
						var shippingZip = '';
						var shippingCountry = '';

						// Check if Shipping address is set.
						if ( typeof json.data.Customer.Shipping === 'object' && 'zip' in json.data.Customer.Shipping && '' !== json.data.Customer.Shipping.zip ) {
							// Set shipping zip and country.
							shippingZip = json.data.Customer.Shipping.zip;
							shippingCountry = json.data.Customer.Shipping.country;
							bco_wc.addressData.shippingAddress = json.data.Customer.Shipping;

							billingAddress = bco_wc.setBillingAddress(json.data);
							
							if ( bco_wc.addressData.shippingZip === shippingZip ) {
								return;
							}
							bco_wc.addressData.updateNeeded = 'yes';
						} else {
							billingAddress = bco_wc.setBillingAddress(json.data);
							shippingZip = billingAddress.billingZip;
							shippingCountry = billingAddress.billingCountry;

							if ( bco_wc.addressData.billingZip === billingAddress.billingZip ) {
								return;
							}
							bco_wc.addressData.updateNeeded = 'yes';
						}

						bco_wc.addressData.billingZip = billingAddress.billingZip;
						bco_wc.addressData.billingCountry = billingAddress.billingCountry;
						bco_wc.addressData.shippingZip = shippingZip;
						bco_wc.addressData.shippingCountry = shippingCountry;


						/**
						 * Customer adress have been set or updated
						 */
						$('#jsLog').append('address_selected<br />');

						if ( 'yes' === bco_wc.addressData.updateNeeded ) {
							$( '.woocommerce-checkout-review-order-table' ).block({
								message: null,
								overlayCSS: {
									background: '#fff',
									opacity: 0.6
								}
							});
							$.ajax(
								{
									url: bco_wc_params.iframe_shipping_address_change_url,
									type: 'POST',
									dataType: 'json',
									data: {
										address: bco_wc.addressData,
										nonce: bco_wc_params.iframe_shipping_address_change_nonce
									},
									success: function( response ) {
										bco_wc.setCustomerData(response.data);
										// All good trigger update_checkout event.
										$( 'body' ).trigger( 'update_checkout' );
									},
									error: function( response ) {
										console.log( response );
									},
									complete: function() {
										bco_wc.shippingUpdated = true;
										$( '.woocommerce-checkout-review-order-table' ).unblock();
									}
								}
							);
						}
						break;
					case 'payment_method_selected':
						/**
						 * Payment method is selected
						 */
						$('#jsLog').append('payment_method_selected<br />');
						break;
					case 'checkout_success':
						/**
						 * Order is paid and customer are not already redirected to accepturl
						 * Store decideds what to do with order and if redirect customer
						 */
						$('#jsLog').append('checkout_success<br />');
						if ( 'checkout' === bco_wc_params.checkout_flow ) {
							var redirectUrl = sessionStorage.getItem( 'billmateRedirectUrl' );
							console.log(redirectUrl);
							if( redirectUrl ) {
								window.location.href = redirectUrl;
							}
						} else {
							$.ajax(
								{
									url: bco_wc_params.checkout_success_url,
									type: 'POST',
									dataType: 'json',
									data: {
										nonce: bco_wc_params.checkout_success_nonce
									},
									success: function() {
									},
									error: function() {
									},
									complete: function( data ) {
										window.location.href = data.responseJSON.data.bco_wc_received_url;
									}
								}
							);
						}
						break;
					case 'content_height':
						/**
						 * The height of checkout, parent can use height to set iframe height when height changes
						 */
						$(document).find('#checkout').height(json.data);
						$('#jsLog').append('content_height: ' + json.data + '<br />');
						break;
					case 'content_scroll_position':
						/**
						 * When checkout iframe page is scrolled 
						 * If iframe have same height as Qvickly Checkout the store can do the scrolling
						 * Only scroll to checkout if enabled in settings.
						 */
						if( 'no' === bco_wc_params.disable_scroll_to_checkout ) {
							window.latestScroll = $(document).find( "#checkout" ).offset().top + json.data;
							$('html, body').animate({scrollTop: $(document).find( "#checkout" ).offset().top + json.data}, 400);
							$('#jsLog').append('content_scroll_position: ' + json.data + '<br />');
						}
						break;
					case 'checkout_loaded':
						$('#jsLog').append('checkout_loaded<br />');
						break;
					case 'hide_overlay':
						/**
						 * Overlay is closed.
						 */
						bco_wc.hide_overlay();
						break;
					case 'go_to':
						/**
						 * Redirect to a URL (app). JSON data includes the URL for the redirection.
						 */
						 location.href = json.data;
						break;
					default:
					break;
				}
			}
		},

		bodyEl: $('body'),
		checkoutFormSelector: 'form.checkout',
		shippingUpdated: false,

		// Order notes.
		orderNotesValue: '',
		orderNotesSelector: 'textarea#order_comments',
		orderNotesEl: $('textarea#order_comments'),

		// Payment method.
		paymentMethodEl: $('input[name="payment_method"]'),
		paymentMethod: '',
		selectAnotherSelector: '#billmate-checkout-select-other',

		// Address data.
		addressData: {
			billingAddress: [],
			billingZip: '',
			billingCountry: '',
			shippingAddress: [],
			shippingZip: '',
			shippingCountry: '',
			updateNeeded: 'no'
		},

		// Extra checkout fields.
		blocked: false,
		extraFieldsSelectorText: 'div#bco-extra-checkout-fields input[type="text"], div#bco-extra-checkout-fields input[type="password"], div#bco-extra-checkout-fields textarea, div#bco-extra-checkout-fields input[type="email"], div#bco-extra-checkout-fields input[type="tel"]',
		extraFieldsSelectorNonText: 'div#bco-extra-checkout-fields select, div#bco-extra-checkout-fields input[type="radio"], div#bco-extra-checkout-fields input[type="checkbox"], div#bco-extra-checkout-fields input.checkout-date-picker, input#terms input[type="checkbox"]',

		setBillingAddress: function(data) {
			var billingZip = '';
			var billingCountry = '';

			// Set billing zip and country.
			if ( typeof data.billingAddress === 'object' && 'zip' in data.billingAddress && '' !== data.billingAddress.zip ) { // Set billing zip and country from billingAddress object.
				billingZip = data.billingAddress.zip;
				billingCountry = data.billingAddress.country;
				bco_wc.addressData.billingAddress = data.billingAddress;
			} else if ( typeof data.Customer.Billing === 'object' && 'zip' in data.Customer.Billing && '' !== data.Customer.Billing.zip ) { // Set billing zip and country from Customer object.
				billingZip = data.Customer.Billing.zip;
				billingCountry = data.Customer.Billing.country;
				bco_wc.addressData.billingAddress = data.Customer.Billing;
			}

			return {
				billingZip:billingZip,
				billingCountry:billingCountry
			   }

		},

		updateBillmateCheckout: function() {
			$('.woocommerce-checkout-review-order-table').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			$.ajax({
				type: 'POST',
				url: bco_wc_params.update_checkout_url,
				data: {
					nonce: bco_wc_params.update_checkout_nonce
				},
				dataType: 'json',
				success: function(data) {
				},
				error: function(data) {
				},
				complete: function(data) {
					console.log(data.responseJSON);
					if (true === data.responseJSON.success) {

						if( data.responseJSON.data && data.responseJSON.data.refreshZeroAmount ) {
							window.location.reload();
						}

						bco_wc.update();
						// bco_wc.unlock();
						$('.woocommerce-checkout-review-order-table').unblock();
					} else {
						console.log('error');
						if( '' !== data.responseJSON.data.redirect_url ) {
							console.log('Cart do not need payment. Reloading checkout.');
							window.location.href = data.responseJSON.data.redirect_url;
						}
					}
				}
			});
		},

		getBillmateCheckout: function() {
			$.ajax({
				type: 'POST',
				url: bco_wc_params.get_checkout_url,
				data: {
					nonce: bco_wc_params.get_checkout_nonce
				},
				dataType: 'json',
				success: function(data) {
				},
				error: function(data) {
					return false;
				},
				complete: function(data) {
					bco_wc.setCustomerData( data.responseJSON.data );
					// Check Terms checkbox, if it exists.
					if ($("form.checkout #terms").length > 0) {
						$("form.checkout #terms").prop("checked", true);
					}
					bco_wc.submitForm();
				}
			});
		},

		setCustomerData: function( data ) {

			// Don't update customer data in Woo if disabled via filter.
			if( 'no' === bco_wc_params.populate_address_fields ) {
				return;
			}

			if ( data.billing_address !== null && data.billing_address !== undefined ) {
				// Billing fields.
				$( '#billing_first_name' ).val( ( ( 'firstname' in data.billing_address ) ? data.billing_address.firstname : '' ) );
				$( '#billing_last_name' ).val( ( ( 'lastname' in data.billing_address ) ? data.billing_address.lastname : '' ) );
				$( '#billing_company' ).val( ( ( 'company' in data.billing_address ) ? data.billing_address.company : '' ) );
				$( '#billing_address_1' ).val( ( ( 'street' in data.billing_address ) ? data.billing_address.street : '' ) );
				$( '#billing_address_2' ).val( ( ( 'street2' in data.billing_address ) ? data.billing_address.street2 : '' ) );
				$( '#billing_city' ).val( ( ( 'city' in data.billing_address ) ? data.billing_address.city : '' ) );
				$( '#billing_postcode' ).val( ( ( 'zip' in data.billing_address ) ? data.billing_address.zip : '' ) );
				$( '#billing_phone' ).val( ( ( 'phone' in data.billing_address ) ? data.billing_address.phone : '' ) );
				$( '#billing_email' ).val( ( ( 'email' in data.billing_address ) ? data.billing_address.email : '' ) );
				$( '#billing_country' ).val( ( ( 'country' in data.billing_address ) ? data.billing_address.country.toUpperCase() : '' ) );
			} else {
				// We did not receive a billing address from Qvickly, let's reload the checkout page an try again.
				window.location.reload();
			}

			if ( data.shipping_address !== null ) {
				$( '#ship-to-different-address-checkbox' ).prop( 'checked', true);

				// Shipping fields.
				// Shipping frst name. Can be returned empty.
				var shipping_first_name = ( 'firstname' in data.shipping_address ) ? data.shipping_address.firstname : '';
				if( 0 === shipping_first_name.length) {
					shipping_first_name = data.billing_address.firstname;
				}
				//Shipping last name. Can be returned empty.
				var shipping_last_name = ( 'lastname' in data.shipping_address ) ? data.shipping_address.lastname : '';
				if( 0 === shipping_last_name.length) {
					shipping_last_name = data.billing_address.lastname;
				}
				$( '#shipping_first_name' ).val( shipping_first_name );
				$( '#shipping_last_name' ).val( shipping_last_name );
				$( '#shipping_company' ).val( ( ( 'company' in data.shipping_address ) ? data.billing_address.company : '' ) );
				$( '#shipping_address_1' ).val( ( ( 'street' in data.shipping_address ) ? data.shipping_address.street : '' ) );
				$( '#shipping_address_2' ).val( ( ( 'street2' in data.shipping_address ) ? data.shipping_address.street2 : '' ) );
				$( '#shipping_city' ).val( ( ( 'city' in data.shipping_address ) ? data.shipping_address.city : '' ) );
				$( '#shipping_postcode' ).val( ( ( 'zip' in data.shipping_address ) ? data.shipping_address.zip : '' ) );
				$( '#shipping_country' ).val( ( ( 'country' in data.shipping_address ) ? data.shipping_address.country.toUpperCase() : '' ) );
			} else {

				$( '#shipping_first_name' ).val( ( ( 'firstname' in data.billing_address ) ? data.billing_address.firstname : '' ) );
				$( '#shipping_last_name' ).val( ( ( 'lastname' in data.billing_address ) ? data.billing_address.lastname : '' ) );
				$( '#shipping_company' ).val( ( ( 'company' in data.billing_address ) ? data.billing_address.company : '' ) );
				$( '#shipping_address_1' ).val( ( ( 'street' in data.billing_address ) ? data.billing_address.street : '' ) );
				$( '#shipping_address_2' ).val( ( ( 'street2' in data.billing_address ) ? data.billing_address.street2 : '' ) );
				$( '#shipping_city' ).val( ( ( 'city' in data.billing_address ) ? data.billing_address.city : '' ) );
				$( '#shipping_postcode' ).val( ( ( 'zip' in data.billing_address ) ? data.billing_address.zip : '' ) );
				$( '#shipping_country' ).val( ( ( 'country' in data.billing_address ) ? data.billing_address.country.toUpperCase() : '' ) );
			}
		},

		/*
		* Document ready function. 
		* Runs on the $(document).ready event.
		*/
		documentReady: function() {
			bco_wc.moveExtraCheckoutFields();
		},

		// When "Change to another payment method" is clicked.
		changeFromBCO: function(e) {
			e.preventDefault();

			$(bco_wc.checkoutFormSelector).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {
					bco: false,
					nonce: bco_wc_params.change_payment_method_nonce
				},
				url: bco_wc_params.change_payment_method_url,
				success: function (data) {},
				error: function (data) {},
				complete: function (data) {
					window.location.href = data.responseJSON.data.redirect;
				}
			});
		},

		// When payment method is changed to BCO in regular WC Checkout page.
		maybeChangeToBCO: function() {
			if ( 'bco' === $(this).val() ) {

				$(bco_wc.checkoutFormSelector).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				$('.woocommerce-info').remove();

				$.ajax({
					type: 'POST',
					data: {
						bco: true,
						nonce: bco_wc_params.change_payment_method_nonce
					},
					dataType: 'json',
					url: bco_wc_params.change_payment_method_url,
					success: function (data) {},
					error: function (data) {},
					complete: function (data) {
						window.location.href = data.responseJSON.data.redirect;
					}
				});
			}
		},

		/**
		* Moves all non standard fields to the extra checkout fields.
		*/
		moveExtraCheckoutFields: function() {
			// Move order comments.
			$( '.woocommerce-additional-fields' ).appendTo( '#bco-extra-checkout-fields' );

			var form = $( 'form[name="checkout"] input, form[name="checkout"] select, textarea' );
			for ( i = 0; i < form.length; i++ ) {
				var name = form[i].name.replace('[]', '\\[\\]'); // Escape any empty "array" keys to prevent errors.

				// Check if field is inside the order review.
				if( $( 'table.woocommerce-checkout-review-order-table' ).find( form[i] ).length ) {
					continue;
				}

				// Check if this is a standard field.
				if ( -1 === $.inArray( name, bco_wc_params.standard_woo_checkout_fields ) ) {

					// This is not a standard Woo field, move to our div.
					if ( 0 < $( 'p#' + name + '_field' ).length ) {
						$( 'p#' + name + '_field' ).appendTo( '#bco-extra-checkout-fields' );
					} else {
						$( 'input[name="' + name + '"]' ).closest( 'p' ).appendTo( '#bco-extra-checkout-fields' );
					}
				}
			}
		},

		/*
		* Check if our gateway is the selected gateway.
		*/
		checkIfSelected: function() {
			if (bco_wc.paymentMethodEl.length > 0) {
				bco_wc.paymentMethod = bco_wc.paymentMethodEl.filter(':checked').val();
				if( 'bco' === bco_wc.paymentMethod ) {
					return true;
				}
			} 
			return false;
		},

		/**
		 * Submit the order using the WooCommerce AJAX function.
		 */
		submitForm: function() {
			$( '.woocommerce-checkout-review-order-table' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			$.ajax({
				type: 'POST',
				url: bco_wc_params.submit_order,
				data: $('form.checkout').serialize(),
				dataType: 'json',
				success: function( data ) {
					try {
						if ( 'success' === data.result ) {
							bco_wc.logToFile( 'Successfully placed order. Sending bco_order_validation true to Qvickly' );

							$( 'body' ).trigger( 'bco_order_validation', true );
							sessionStorage.setItem( 'billmateRedirectUrl', data.redirect_url );
							$('form.checkout').removeClass( 'processing' ).unblock();
						} else {
							throw 'Result failed';
						}
					} catch ( err ) {
						if ( data.messages )  {
							bco_wc.logToFile( 'Checkout error | ' + data.messages );
							bco_wc.failOrder( 'submission', data.messages );
						} else {
							bco_wc.logToFile( 'Checkout error | No message' );
							bco_wc.failOrder( 'submission', '<div class="woocommerce-error">' + 'Checkout error' + '</div>' );
						}
					}
				},
				error: function( data ) {
					bco_wc.logToFile( 'AJAX error | ' + data );
					bco_wc.failOrder( 'ajax-error', data );
				}
			});
		},

		failOrder: function( event, error_message ) {
			// Send false and cancel.
			console.log('failOrder');
			$( 'body' ).trigger( 'bco_order_validation', false );

			// Abort waiting in checkout.
			checkout_iframe.postMessage('abort_waiting', '*');

			// Re-enable the form.
			$( 'body' ).trigger( 'updated_checkout' );
			$( bco_wc.checkoutFormSelector ).unblock();
			$( '.woocommerce-checkout-review-order-table' ).unblock();

			// Print error messages, and trigger checkout_error, and scroll to notices.
			$( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
			$( 'form.checkout' ).prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>' ); // eslint-disable-line max-len
			$( 'form.checkout' ).removeClass( 'processing' ).unblock();
			$( 'form.checkout' ).find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();
			$( document.body ).trigger( 'checkout_error' , [ error_message ] );
			$( 'html, body' ).animate( {
				scrollTop: ( $( 'form.checkout' ).offset().top - 100 )
			}, 1000 );
		},

		logToFile: function( message ) {
			$.ajax(
				{
					url: bco_wc_params.log_to_file_url,
					type: 'POST',
					dataType: 'json',
					data: {
						message: message,
						nonce: bco_wc_params.log_to_file_nonce
					}
				}
			);
		},

		init: function() {
			window.addEventListener("message", bco_wc.handleEvent);
			// Check if Qvickly is the selected payment method before we do anything.
			if( bco_wc.checkIfSelected() ) {
				$(document).ready( bco_wc.documentReady() );

				// Change from BCO.
				bco_wc.bodyEl.on('click', bco_wc.selectAnotherSelector, bco_wc.changeFromBCO);

				if ( 'checkout' === bco_wc_params.checkout_flow ) {
					// Update Qvickly payment.
					bco_wc.bodyEl.on('updated_checkout', bco_wc.updateBillmateCheckout);
					// Lock Qvickly on update_checkout.
					// bco_wc.bodyEl.on('update_checkout', bco_wc.lock);
				}
			}
			bco_wc.bodyEl.on('change', 'input[name="payment_method"]', bco_wc.maybeChangeToBCO);
			bco_wc.bodyEl.on( 'click', bco_wc.selectAnotherSelector, bco_wc.changeFromBCO );
		},
	}
	bco_wc.init();
});

/***/ })

/******/ });
//# sourceMappingURL=bco-checkout.min.js.map