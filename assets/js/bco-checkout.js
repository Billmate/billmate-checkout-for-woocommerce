jQuery(function($) {
    const bco_wc = {

        update: function() {

            /**
             * Refresh checkout in iframe
             * In this example the name of iframe that contain Billmate Checkout have the name checkout_iframe
             */
            checkout_iframe.postMessage('update', '*');
        },
    
        lock: function() {
    
            /**
             * Lock Billmate checkout from customer interactions
             * Will display loading animation and is used when the store is working on the order, for example when cart is updated
             */
            checkout_iframe.postMessage('lock', '*');
        },
    
        unlock: function() {
    
            /**
             * If checkout is locked, remove loading animation and customer can continue to interact with Billmate Checkout
             * Is used when store is done with work that affect the order and Billmate Checkout, for example when cart is updated
             */
            checkout_iframe.postMessage('unlock', '*');
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
						bco_wc.getBillmateCheckout();

						$( 'body' ).on( 'bco_order_validation', function( event, bool ) {
							if ( true === bool ) {
								// Success.
								bco_wc.unlock();
							}
						});

						break;
                    case 'address_selected':
						var billingZip = '';
						var billingCountry = '';
						var shippingZip = '';
						var shippingCountry = '';

						// Check if Shipping address is set.
						if ( typeof json.data.Customer.Shipping === 'object' && 'zip' in json.data.Customer.Shipping && '' !== json.data.Customer.Shipping.zip ) {
							// Set shipping zip and country.
							shippingZip = json.data.Customer.Shipping.zip;
							shippingCountry = json.data.Customer.Shipping.country;
							bco_wc.addressData.shippingAddress = json.data.Customer.Shipping;

							// Set billing zip and country.
							if ( typeof json.data.billingAddress === 'object' && 'zip' in json.data.billingAddress && '' !== json.data.billingAddress.zip ) { // Set billing zip and country from billingAddress object.
								billingZip = json.data.billingAddress.zip;
								billingCountry = json.data.billingAddress.country;
								bco_wc.addressData.billingAddress = json.data.billingAddress;
							} else if ( typeof json.data.Customer.Billing === 'object' && 'zip' in json.data.Customer.Billing && '' !== json.data.Customer.Billing.zip ) { // Set billing zip and country from Customer object.
								billingZip = json.data.Customer.Billing.zip;
								billingCountry = json.data.Customer.Billing.country;
								bco_wc.addressData.billingAddress = json.data.Customer.Billing;
							}
							
							if ( bco_wc.addressData.shippingZip === shippingZip ) {
								return;
							}
							bco_wc.addressData.updateNeeded = 'yes';
						} else {
							// Set billing zip and country.
							if ( typeof json.data.billingAddress === 'object' && 'zip' in json.data.billingAddress && '' !== json.data.billingAddress.zip ) { // Set billing zip and country from billingAddress object.
								billingZip = json.data.billingAddress.zip;
								billingCountry = json.data.billingAddress.country;
								bco_wc.addressData.billingAddress = json.data.billingAddress;
							} else if ( typeof json.data.Customer.Billing === 'object' && 'zip' in json.data.Customer.Billing && '' !== json.data.Customer.Billing.zip ) { // Set billing zip and country from Customer object.
								billingZip = json.data.Customer.Billing.zip;
								billingCountry = json.data.Customer.Billing.country;
								bco_wc.addressData.billingAddress = json.data.Customer.Billing;
							}

							if ( bco_wc.addressData.billingZip === billingZip ) {
								return;
							}
							bco_wc.addressData.updateNeeded = 'yes';
						}
		
						bco_wc.addressData.billingZip = billingZip;
						bco_wc.addressData.billingCountry = billingCountry;
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
									error: function() {
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
                         * If iframe have same height as Billmate Checkout the store can do the scrolling 
                         */
                        window.latestScroll = $(document).find( "#checkout" ).offset().top + json.data;
                        $('html, body').animate({scrollTop: $(document).find( "#checkout" ).offset().top + json.data}, 400);
    
                        $('#jsLog').append('content_scroll_position: ' + json.data + '<br />');
    
                        break;
                    case 'checkout_loaded':
    
                        $('#jsLog').append('checkout_loaded<br />');
    
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
						bco_wc.update();
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
					$('form.checkout').submit();
					return true;
				}
			});
		},

		setCustomerData: function( data ) {
			if ( data.billing_address !== null ) {
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
			}
			
			if ( data.shipping_address !== null ) {
				$( '#ship-to-different-address-checkbox' ).prop( 'checked', true);

				// Shipping fields.
				$( '#shipping_first_name' ).val( ( ( 'firstname' in data.shipping_address ) ? data.shipping_address.firstname : '' ) );
				$( '#shipping_last_name' ).val( ( ( 'lastname' in data.shipping_address ) ? data.shipping_address.lastname : '' ) );
				$( '#shipping_company' ).val( ( ( 'company' in data.shipping_address ) ? data.billing_address.company : '' ) );
				$( '#shipping_address_1' ).val( ( ( 'street' in data.shipping_address ) ? data.shipping_address.street : '' ) );
				$( '#shipping_address_2' ).val( ( ( 'street2' in data.shipping_address ) ? data.shipping_address.street2 : '' ) );
				$( '#shipping_city' ).val( ( ( 'city' in data.shipping_address ) ? data.shipping_address.city : '' ) );
				$( '#shipping_postcode' ).val( ( ( 'zip' in data.shipping_address ) ? data.shipping_address.zip : '' ) );
				$( '#shipping_country' ).val( ( ( 'country' in data.shipping_address ) ? data.shipping_address.country.toUpperCase() : '' ) );
			}
		},

		hashChange: function() {
			console.log('hashchange');
			var currentHash = location.hash;
            var splittedHash = currentHash.split("=");
            console.log(splittedHash[0]);
            console.log(splittedHash[1]);
            if(splittedHash[0] === "#billmate-success"){
				$( 'body' ).trigger( 'bco_order_validation', true );
				var response = JSON.parse( atob( splittedHash[1] ) );
                console.log('response.redirect_url');
                console.log(response.redirect_url);
				sessionStorage.setItem( 'billmateRedirectUrl', response.redirect_url );
				$('form.checkout').removeClass( 'processing' ).unblock();
            }
		},

		errorDetected: function() {
			$( 'body' ).trigger( 'bco_order_validation', false );
		},

        /*
		 * Document ready function. 
		 * Runs on the $(document).ready event.
		 */
		documentReady: function() {
			bco_wc.moveExtraCheckoutFields();

			if ( 'checkout' === bco_wc_params.checkout_flow ) {
				// Add two column class to checkout if Billmate setting in Woo is set.
				if ( 'two_column_checkout' === bco_wc_params.checkout_layout ) {
					$('form.checkout.woocommerce-checkout').addClass('bco-two-column-checkout-left');
					$('#bco-iframe').addClass('bco-two-column-checkout-right');
				}
			}
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
				var name = form[i].name;

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


        init: function() {
			window.addEventListener("message", bco_wc.handleEvent);
            // Check if Billmate is the selected payment method before we do anything.
			if( bco_wc.checkIfSelected() ) {
                $(document).ready( bco_wc.documentReady() );

                // Change from BCO.
                bco_wc.bodyEl.on('click', bco_wc.selectAnotherSelector, bco_wc.changeFromBCO);

				if ( 'checkout' === bco_wc_params.checkout_flow ) {
					// Update Billmate payment.
					bco_wc.bodyEl.on('updated_checkout', bco_wc.updateBillmateCheckout); 					
				}
                
                // Hashchange.
                $( window ).on('hashchange', bco_wc.hashChange);
                
				// Error detected.
				$( document.body ).on( 'checkout_error', bco_wc.errorDetected );
            }
            bco_wc.bodyEl.on('change', 'input[name="payment_method"]', bco_wc.maybeChangeToBCO);
			bco_wc.bodyEl.on( 'click', bco_wc.selectAnotherSelector, bco_wc.changeFromBCO );
        },
    }
    bco_wc.init();
});

