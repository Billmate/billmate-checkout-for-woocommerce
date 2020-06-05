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
		$order              = wc_get_order( $order_id );
		$bco_payment_number = $bco_checkout['data']['PaymentData']['number'];
		$bco_order_number   = $bco_checkout['data']['PaymentData']['order']['number'];
		switch ( strtolower( $bco_checkout['data']['PaymentData']['order']['status'] ) ) {
			case 'pending':
				// Translators: Billmate pyment number.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Payment number: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_payment_number ) );
				$order->add_order_note( $note );
				update_post_meta( $order_id, '_billmate_payment_number', $bco_payment_number );
				update_post_meta( $order_id, '_billmate_order_number', $bco_order_number );
				self::$order_is_valid = true;
				break;
			case 'created':
				// Translators: Billmate pyment number.
				$note = sprintf( __( 'Payment via Billmate Checkout. Payment number: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_payment_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_payment_number );

				update_post_meta( $order_id, '_billmate_payment_number', $bco_payment_number );
				update_post_meta( $order_id, '_billmate_order_number', $bco_order_number );
				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				self::$order_is_valid = true;
				break;
			case 'paid':
				// Translators: Billmate pyment number.
				$note = sprintf( __( 'Payment via Billmate Checkout. Payment number: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_payment_number ) );
				$order->add_order_note( $note );
				$order->payment_complete( $bco_payment_number );

				update_post_meta( $order_id, '_billmate_payment_number', $bco_payment_number );
				update_post_meta( $order_id, '_billmate_order_number', $bco_order_number );
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
