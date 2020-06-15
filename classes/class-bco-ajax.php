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
	 * Order is valid flag.
	 *
	 * @var boolean
	 */
	public static $order_is_valid = false;

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
			'bco_wc_update_checkout'  => true,
			'bco_wc_checkout_success' => true,
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
	 * Update checkout.
	 *
	 * @return void
	 */
	public static function bco_wc_update_checkout() {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		if ( 'bco' === WC()->session->get( 'chosen_payment_method' ) ) {

			$bco_wc_hash = WC()->session->get( 'bco_wc_hash' );

			// Set empty return array for errors.
			$return = array();

			// Check if we have a Billmate checkout hash.
			if ( empty( $bco_wc_hash ) ) {
				wc_add_notice( 'Billmate checkout hash is missing.', 'error' );
				wp_send_json_error();
				wp_die();
			} else {
				// Get the Billmate checkout.
				$billmate_checkout = BCO_WC()->api->request_get_checkout( $bco_wc_hash );
				// Check if we got a wp_error.
				if ( ! $billmate_checkout ) {
					wp_send_json_error();
					wp_die();
				}

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
				$billmate_order = BCO_WC()->api->request_update_checkout( $billmate_checkout['data']['PaymentData']['number'] );
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

		if ( is_object( $order ) ) {
			if ( ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {
				// Retrieve the Billmate order number from get_checkout request.
				$bco_checkout = BCO_WC()->api->request_get_checkout();

				if ( ! isset( $bco_checkout['code'] ) && isset( $bco_checkout['data']['PaymentData']['order']['status'] ) ) {
					$bco_order_number = ( isset( $bco_checkout['data']['PaymentData']['order']['number'] ) ) ? $bco_checkout['data']['PaymentData']['order']['number'] : '';
					update_post_meta( $order_id, '_transaction_id', $bco_order_number );

					// Make get_payment request if we have Billmate order number.
					if ( '' !== $bco_order_number ) {
						$bco_order = BCO_WC()->api->request_get_payment( $bco_order_number );
					}
					// Set payment method title and confirm order.
					bco_set_payment_method_title( $order_id, $bco_order );
					self::bco_confirm_billmate_order( $order_id, $bco_checkout );
				}
			}
		}

		if ( true === self::$order_is_valid ) {
			$data = array( 'bco_wc_received_url' => $order->get_checkout_order_received_url() );
			wp_send_json_success( $data );
			wp_die();
		} else {
			wp_send_json_error();
			wp_die();
		}
	}

	/**
	 * Confirm Billmate order.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @param array  $bco_checkout The Billmate checkout.
	 * @return void
	 */
	public static function bco_confirm_billmate_order( $order_id, $bco_checkout = array() ) {
		$order            = wc_get_order( $order_id );
		$bco_order_number = $bco_checkout['data']['PaymentData']['order']['number'];
		switch ( strtolower( $bco_checkout['data']['PaymentData']['order']['status'] ) ) {
			case 'pending':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->update_status( 'on-hold' );

				update_post_meta( $order_id, '_billmate_transaction_id', $bco_order_number );
				self::$order_is_valid = true;
				break;
			case 'created':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_order_number );

				update_post_meta( $order_id, '_billmate_transaction_id', $bco_order_number );
				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				self::$order_is_valid = true;
				break;
			case 'paid':
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_order_number );

				update_post_meta( $order_id, '_billmate_transaction_id', $bco_order_number );
				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				self::$order_is_valid = true;
				break;
			case 'cancelled':
				break;
			case 'failed':
				break;
		}
	}
}
BCO_AJAX::init();
