<?php
/**
 * API Callbacks class.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BCO_API_Callbacks class.
 *
 * Class that handles BCO API callbacks.
 */
class BCO_API_Callbacks {
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
	 * BCO_API_Callbacks constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_bco_wc_push', array( $this, 'push_cb' ) );
		add_action( 'bco_process_push_callback', array( $this, 'process_push_callback' ) );
	}

	/**
	 * Push callback function.
	 */
	public function push_cb() {
		$post           = file_get_contents( 'php://input' );
		$data           = json_decode( $post, true );
		$transaction_id = sanitize_key( $data['data']['number'] );
		$bco_status     = strtolower( sanitize_key( $data['data']['status'] ) );

		if ( ! isset( $data['credentials'] ) || ! isset( $data['credentials']['hash'] ) ) {
			BCO_Logger::log( 'Push callback hit but no credentials found in callback data.' );
			status_header( 401, 'Missing credentials' );
			wp_die();
		}

		$credentials  = $data['credentials']['hash'];
		$datastr      = json_encode( $data['data'] );
		$bco_settings = get_option( 'woocommerce_bco_settings' );

		$hash = hash_hmac( 'sha512', $datastr, $bco_settings['api_key_se'] );
		if ( $hash !== $credentials ) {
			BCO_Logger::log( 'Push callback hit but credentials did not match.' );
			status_header( 401, 'Bad credentials' );
			wp_die();
		}
		// Get the WC order ID from the post meta field _billmate_saved_woo_order_no.
		// If that doesn't exist, try getting it from the WC order transaction ID.
		$order_id = bco_get_order_id_by_billmate_saved_woo_order_no( $data['data']['orderid'] );
		if ( empty( $order_id ) ) {
			$order_id = bco_get_order_id_by_transaction_id( sanitize_text_field( $data['data']['number'] ) );
		}

		// Log the request.
		BCO_Logger::log( 'Push callback hit for order_id: ' . $order_id . '. Temp order id: ' . $data['data']['orderid'] . '. Qvickly transaction id: ' . $transaction_id . '. Qvickly status: ' . $bco_status . '. Scheduling event to execute order status check in 2 minutes.' );
		$process_data = array(
			'order_id'   => $order_id,
			'bco_status' => $bco_status,
			'bco_number' => $transaction_id,
		);
		as_schedule_single_action( time() + 120, 'bco_process_push_callback', array( $process_data ) );
	}

	/**
	 * Process the Push callback on a scheduled event.
	 *
	 * @param array $process_data Data returned from Qvickly in the original callback.
	 *
	 * @return void.
	 */
	public function process_push_callback( $process_data ) {
		BCO_Logger::log( 'Process Push callback for order_id: ' . $process_data['order_id'] );

		$order_id = $process_data['order_id'];
		$order    = wc_get_order( $order_id );

		// Abort the callback if we already have received a callback about the payment being Denied.
		if ( is_object( $order ) && 'yes' === get_post_meta( $order_id, '_billmate_payment_denied', true ) ) {
			return;
		}

		// Maybe abort the callback (if the order already has been processed in Woo).
		if ( is_object( $order ) && ! empty( $order->get_date_paid() ) ) {
			// If this ia a cancel order request from Qvickly Online lets cancel the order in Woo.
			if ( 'cancelled' === $process_data['bco_status'] ) {
				// Translators: Qvickly transaction id.
				$note = sprintf( __( 'Order cancelled in Qvickly Online. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
				$order->update_status( 'cancelled', $note );
			} else {
				BCO_Logger::log( 'Aborting Process Push callback for order_id: ' . $process_data['order_id'] . '. Order already have a paid date.' );
				return;
			}
		}

		if ( is_object( $order ) && ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			switch ( $process_data['bco_status'] ) {
				case 'pending':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Order is still PENDING APPROVAL by Qvickly. Please visit Qvickly Online for the latest status on this order. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					$order->update_status( 'on-hold' );
					break;
				case 'created':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Payment via Qvickly Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					$order->payment_complete( $process_data['bco_number'] );
					break;
				case 'paid':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Payment via Qvickly Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					$order->payment_complete( $process_data['bco_number'] );
					break;
				case 'approved':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Payment reported Approved from Qvickly Online. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					$order->payment_complete( $process_data['bco_number'] );
					break;
				case 'denied':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Order reported Denied from Qvickly Online. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					update_post_meta( $order_id, '_billmate_payment_denied', 'yes' );
					$order->update_status( 'failed', $note );
					// Trigger bco_callback_denied_order action so we can cancel the order in BO from Qvickly Order Management plugin.
					do_action( 'bco_callback_denied_order', $order_id );
					break;
				case 'cancelled':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Order reported Cancelled from Qvickly Online. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->update_status( 'cancelled', $note );
					break;
				case 'failed':
					// Translators: Qvickly transaction id.
					$note = sprintf( __( 'Order reported Failed from Qvickly Online. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->update_status( 'failed', $note );
					break;
			}
		}
	}
}
