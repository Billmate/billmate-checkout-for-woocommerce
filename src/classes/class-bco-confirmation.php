<?php
/**
 * Confirmation class file.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Confirmation class.
 */
class BCO_Confirmation {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;
	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return self::$instance The *Singleton* instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'confirm_order' ) );
	}

	/**
	 * Confirm Qvickly order.
	 *
	 * @return void
	 */
	public function confirm_order() {
        if ( isset( $_GET['bco_confirm'] ) && isset( $_GET['wc_order_id'] ) && isset( $_GET['bco_flow']) ) { // phpcs:ignore
			$bco_flow    = filter_input( INPUT_GET, 'bco_flow', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$wc_order_id = filter_input( INPUT_GET, 'wc_order_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$raw_data    = file_get_contents( 'php://input' );
			parse_str( urldecode( $raw_data ), $result );

			// Make sure we have data param in body.
			if ( isset( $result['data'] ) ) {
				$data = json_decode( $result['data'], true );
			} else {
				$data = array();
			}

			if ( isset( $wc_order_id ) && ! empty( $wc_order_id ) && 'null' !== $wc_order_id ) {
				$order_id = $wc_order_id;
			} else {
				if ( substr( $data['orderid'], 0, 3 ) === 'tmp' ) {
					$order_id = bco_get_order_id_by_temp_order_id( sanitize_text_field( $data['orderid'] ) );
				} else {
					$order_id = bco_get_order_id_by_billmate_saved_woo_order_no( $data['orderid'] );
				}
			}

			BCO_Logger::log( 'Confirm order triggered. WC order ID: ' . wp_json_encode( $order_id ) );

			$order = wc_get_order( $order_id );

			// If we don't find the order, log it and return.
			if ( ! is_object( $order ) ) {
				BCO_Logger::log( 'Confirm order step failed. Could not find order. Returned data from Qvickly: ' . wp_json_encode( $data ) );
				return;
			}

			// If the order is already completed, return.
			if ( ! empty( $order->get_date_paid() ) ) {
				BCO_Logger::log( 'Confirm order step aborted. Paid date exist. WC order ID: ' . wp_json_encode( $order_id ) );
				return;
			}

			// If the confirm step already has started by customer once (can happen when payment_complete takes long time to finish), return.
			if ( ! empty( $order->get_meta( '_billmate_confirm_started', true ) ) ) {
				BCO_Logger::log( 'Confirm order step aborted. Confirm step already started. WC order ID: ' . wp_json_encode( $order_id ) );
				return;
			}

			// Save meta data field to keep track of that confirm step has been initialized.
			$order->update_meta_data( '_billmate_confirm_started', 'yes' );
			$order->save();

			// Get the Qvickly checkout object.
			$bco_checkout = BCO_WC()->api->request_get_checkout( get_post_meta( $order_id, '_billmate_hash', true ) );

			if ( 'pay_for_order_redirect' === $bco_flow ) {

				update_post_meta( $order_id, '_billmate_transaction_id', $data['number'] );

				// Get Checkout and set payment method title.
				bco_set_payment_method_title( $order_id, $bco_checkout );

				bco_confirm_billmate_redirect_order( $order_id, $order, $data ); // Confirm.
				bco_wc_unset_sessions(); // Unset Qvickly session data.
				return;

			} elseif ( 'checkout_redirect' === $bco_flow ) {

				update_post_meta( $order_id, '_billmate_transaction_id', $data['number'] );

				// Set payment method title.
				bco_set_payment_method_title( $order_id, $bco_checkout );

				bco_maybe_add_invoice_fee( $order ); // Maybe set invoice fee in WC order.

				bco_confirm_billmate_redirect_order( $order_id, $order, $data ); // Confirm.
				bco_wc_unset_sessions(); // Unset Qvickly session data.
                wp_redirect( $order->get_checkout_order_received_url() ); // phpcs:ignore
				exit;

			} elseif ( 'checkout' === $bco_flow ) {

				if ( false !== $bco_checkout ) {
					update_post_meta( $order_id, '_billmate_transaction_id', $bco_checkout['data']['PaymentData']['order']['number'] );

					// Set payment method title.
					bco_set_payment_method_title( $order_id, $bco_checkout );

					bco_maybe_add_invoice_fee( $order ); // Maybe set invoice fee in WC order.

					bco_confirm_billmate_order( $order_id, $bco_checkout ); // Confirm order.
					bco_wc_unset_sessions(); // Unset Qvickly session data.
				}

				return;
			}
		}
	}


}
BCO_Confirmation::get_instance();
