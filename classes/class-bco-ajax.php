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
		$order = wc_get_order( WC()->session->get( 'bco_wc_order_id' ) );

		wp_send_json_success(
			array(
				'bco_wc_received_url' => $order->get_checkout_order_received_url(),
			)
		);
		wp_die();
	}
}
BCO_AJAX::init();
