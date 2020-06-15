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
                    case 'address_selected':
    
                        /**
                         * Customer adress have been set or updated
                         */
                         $('#jsLog').append('address_selected<br />');
    
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
    
                        /** Checkout done loading , unlock it just in case it is locked*/
                        bco_wc.unlock();
    
                        $('#jsLog').append('checkout_loaded<br />');
    
                        break;
                    default:
                        break;
    
                }
            }
        },

        bodyEl: $('body'),
		checkoutFormSelector: 'form.checkout',

		// Order notes.
		orderNotesValue: '',
		orderNotesSelector: 'textarea#order_comments',
		orderNotesEl: $('textarea#order_comments'),

		// Payment method.
		paymentMethodEl: $('input[name="payment_method"]'),
		paymentMethod: '',
		selectAnotherSelector: '#billmate-checkout-select-other',

		// Address data.
		addressData: [],

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

        /*
		 * Document ready function. 
		 * Runs on the $(document).ready event.
		 */
		documentReady: function() {
			bco_wc.moveExtraCheckoutFields();

			// Add two column class to checkout if Billmate setting in Woo is set.
			/* if ( true === bco_wc_params.bco_checkout_layout.two_column ) {
				$('form.checkout.woocommerce-checkout').addClass('bco-two-column-checkout-left');
				$('#bco-iframe').addClass('bco-two-column-checkout-right');
			} */
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
                
                // Catch changes to order notes.
				bco_wc.bodyEl.on('change', '#order_comments', bco_wc.updateOrderComment);

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

