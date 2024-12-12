<?php // phpcs:ignore
/**
 * Ajax class file.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ajax class.
 */
class BCO_AJAX extends WC_AJAX {
	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'bco_wc_update_checkout'                => true,
			'bco_wc_get_checkout'                   => true,
			'bco_wc_iframe_shipping_address_change' => true,
			'bco_wc_checkout_success'               => true,
			'bco_wc_change_payment_method'          => true,
			'bco_wc_log_js'                         => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				// WC AJAX can be used for frontend ajax requests.
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Change payment method.
	 *
	 * @return void
	 */
	public static function bco_wc_change_payment_method() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bco_wc_change_payment_method' ) ) {
			wp_send_json_error( 'bad_nonce' );
			exit;
		}
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$bco_payment_method = isset( $_POST['bco'] ) ? sanitize_key( $_POST['bco'] ) : '';
		if ( 'false' === $bco_payment_method ) {
			// Set chosen payment method to first gateway that is not ours for WooCommerce.
			$first_gateway = reset( $available_gateways );
			if ( 'bco' !== $first_gateway->id ) {
				WC()->session->set( 'chosen_payment_method', $first_gateway->id );
			} else {
				$second_gateway = next( $available_gateways );
				WC()->session->set( 'chosen_payment_method', $second_gateway->id );
			}
		} else {
			WC()->session->set( 'chosen_payment_method', 'bco' );
		}
		WC()->payment_gateways()->set_current_gateway( $available_gateways );
		$redirect = wc_get_checkout_url();
		$data     = array(
			'redirect' => $redirect,
		);
		wp_send_json_success( $data );
		wp_die();
	}

	/**
	 * Update checkout.
	 *
	 * @return void
	 */
	public static function bco_wc_update_checkout() {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		if ( 'bco' === WC()->session->get( 'chosen_payment_method' ) ) {

			if ( ! WC()->cart->needs_payment() ) {
				wp_send_json_success(
					array(
						'refreshZeroAmount' => 'refreshZeroAmount',
					)
				);
				wp_die();
			}

			$bco_wc_hash = WC()->session->get( 'bco_wc_hash' );

			// Set empty return array for errors.
			$return = array();

			// Check if we have a Qvickly checkout hash and that the currency is correct.
			if ( empty( $bco_wc_hash ) || get_woocommerce_currency() !== WC()->session->get( 'bco_currency' ) ) {
				bco_wc_unset_sessions();
				$return['redirect_url'] = wc_get_checkout_url();
				wp_send_json_error( $return );
				wp_die();
			} else {

				// Calculate cart totals.
				WC()->cart->calculate_fees();
				WC()->cart->calculate_totals();

				// Check if order needs payment.
				if ( apply_filters( 'bco_check_if_needs_payment', true ) ) {
					if ( ! WC()->cart->needs_payment() ) {
						$return['redirect_url'] = wc_get_checkout_url();
						wp_send_json_error( $return );
						wp_die();
					}
				}

				// Update order.
				$billmate_order = BCO_WC()->api->request_update_checkout( WC()->session->get( 'bco_wc_number' ) );
				// If the update failed - reload the checkout page and display the error.
				if ( ! $billmate_order ) {
					wp_send_json_error();
					wp_die();
				}
			}
		}
		// Everything is okay if we get here. Send empty success and kill wp.
		wp_send_json_success();
		wp_die();

	}

	/**
	 * Get checkout.
	 *
	 * @return void
	 */
	public static function bco_wc_get_checkout() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bco_wc_get_checkout' ) ) { // Input var okay.
			wp_send_json_error( 'bad_nonce' );
			exit;
		}

		$billmate_checkout = BCO_WC()->api->request_get_checkout( WC()->session->get( 'bco_wc_hash' ) );

		if ( ! $billmate_checkout ) {
			wp_send_json_error( $billmate_checkout );
			wp_die();
		}
		wp_send_json_success(
			array(
				'billing_address'  => ! empty( $billmate_checkout['data']['Customer']['Billing'] ) ? $billmate_checkout['data']['Customer']['Billing'] : null,
				'shipping_address' => ! empty( $billmate_checkout['data']['Customer']['Shipping'] ) ? $billmate_checkout['data']['Customer']['Shipping'] : null,
			)
		);
		wp_die();
	}


	/**
	 * Shipping address change.
	 *
	 * @return void
	 */
	public static function bco_wc_iframe_shipping_address_change() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bco_wc_iframe_shipping_address_change' ) ) { // Input var okay.
			wp_send_json_error( 'bad_nonce' );
			exit;
		}

		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$bco_payment_number = WC()->session->get( 'bco_wc_number' );

		// Check if we have a Qvickly payment number.
		if ( empty( $bco_payment_number ) ) {
			wc_add_notice( 'Qvickly payment number is missing.', 'error' );
			wp_send_json_error();
			wp_die();
		}

		$customer_data    = array();
		$address_data     = ( isset( $_REQUEST['address'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['address'] ) ) : null;
		$shipping_address = ( isset( $_REQUEST['address']['shippingAddress'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['address']['shippingAddress'] ) ) : null;
		$billing_address  = ( isset( $_REQUEST['address']['billingAddress'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['address']['billingAddress'] ) ) : null;

		// If we have shipping zip and country, use it for calculation. Else use billing zip and country for calculation.
		if ( ! empty( $address_data['shippingZip'] ) && ! empty( $address_data['shippingCountry'] ) ) {
			$shipping_zip     = $address_data['shippingZip'];
			$shipping_country = strtoupper( $address_data['shippingCountry'] );

		} elseif ( ! empty( $address_data['billingZip'] ) && ! empty( $address_data['billingCountry'] ) ) {
			$billing_zip     = $address_data['billingZip'];
			$billing_country = strtoupper( $address_data['billingCountry'] );
		}

		if ( ! empty( $shipping_zip ) ) {
			$customer_data['shipping_postcode'] = $shipping_zip;
			$customer_data['billing_postcode']  = $shipping_zip;
		} elseif ( ! empty( $billing_zip ) ) {
			$customer_data['shipping_postcode'] = $billing_zip;
			$customer_data['billing_postcode']  = $billing_zip;
		}

		if ( ! empty( $shipping_country ) ) {
			$customer_data['shipping_country'] = $shipping_country;
			$customer_data['billing_country']  = $shipping_country;
		} elseif ( ! empty( $billing_country ) ) {
			$customer_data['shipping_country'] = $billing_country;
			$customer_data['billing_country']  = $billing_country;
		}

		WC()->customer->set_props( $customer_data );
		WC()->customer->save();

		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		$billmate_order = BCO_WC()->api->request_update_checkout( $bco_payment_number );

		if ( false === $billmate_order ) {
			wp_send_json_error();
			wp_die();
		}

		wp_send_json_success(
			array(
				'billing_address'  => $billing_address,
				'shipping_address' => $shipping_address,
			)
		);
		wp_die();
	}

	/**
	 * Checkout success.
	 *
	 * @return void
	 */
	public static function bco_wc_checkout_success() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bco_wc_checkout_success' ) ) { // Input var okay.
			wp_send_json_error( 'bad_nonce' );
			exit;
		}
		$order_id = WC()->session->get( 'bco_wc_order_id' );
		$order    = wc_get_order( $order_id );

		$bco_checkout = BCO_WC()->api->request_get_checkout( WC()->session->get( 'bco_wc_hash' ) );
		update_post_meta( $order_id, '_billmate_transaction_id', $bco_checkout['data']['PaymentData']['order']['number'] );

		// Set payment method title.
		bco_set_payment_method_title( $order_id, $bco_checkout );

		bco_maybe_add_invoice_fee( $order ); // Maybe set invoice fee in WC order.
		bco_confirm_billmate_order( $order_id, $bco_checkout );

		if ( false !== $bco_checkout ) {
			$data = array( 'bco_wc_received_url' => $order->get_checkout_order_received_url() );
			wp_send_json_success( $data );
			wp_die();
		} else {
			wp_send_json_error();
			wp_die();
		}
	}

	/**
	 * Logs messages from the JavaScript to the server log.
	 *
	 * @return void
	 */
	public static function bco_wc_log_js() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bco_wc_log_js' ) ) {
			wp_send_json_error( 'bad_nonce' );
			exit;
		}
		$posted_message     = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
		$bco_payment_number = WC()->session->get( 'bco_wc_number' );
		$message            = "Frontend JS $bco_payment_number: $posted_message";
		BCO_Logger::log( $message );
		wp_send_json_success();
		wp_die();
	}
}
BCO_AJAX::init();
