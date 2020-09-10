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

		// If the callback is triggered by a new purchase, the returned orderid field is the temporary order id.
		// If the callback is triggered by a change in the Billmate Online portal, the returned orderid field is the WC order number. Then we try to get the WC order based on the Billmate invoice number.
		if ( substr( $data['data']['orderid'], 0, 3 ) === 'tmp' ) {
			$order_id = bco_get_order_id_by_temp_order_id( sanitize_text_field( $data['data']['orderid'] ) );
		} else {
			$order_id = bco_get_order_id_by_transaction_id( sanitize_text_field( $data['data']['number'] ) );
		}

		// Log the request.
		BCO_Logger::log( 'Push callback hit for order_id: ' . $order_id . '. Temp order id: ' . $data['data']['orderid'] . '. Billmate transaction id: ' . $transaction_id . '. Billmate status: ' . $bco_status . '. Scheduling event to execute order status check in 2 minutes.' );
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
	 * @param array $process_data Data returned from Billmate in the original callback.
	 *
	 * @return void.
	 */
	public function process_push_callback( $process_data ) {
		BCO_Logger::log( 'Process Push callback for order_id: ' . $process_data['order_id'] );

		$order_id = $process_data['order_id'];
		$order    = wc_get_order( $order_id );

		// Maybe abort the callback (if the order already has been processed in Woo).
		if ( is_object( $order ) && ! empty( $order->get_date_paid() ) ) {
			// If this ia a cancel order request from Billmate Online lets cancel the order in Woo.
			if ( 'cancelled' === $process_data['bco_status'] ) {
				// Translators: Billmate transaction id.
				$note = sprintf( __( 'Order cancelled in Billmate Online. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
				$order->update_status( 'cancelled', $note );
			} else {
				BCO_Logger::log( 'Aborting Process Push callback for order_id: ' . $process_data['order_id'] . '. Order already have a paid date.' );
				return;
			}
		}

		if ( is_object( $order ) && ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			switch ( $process_data['bco_status'] ) {
				case 'pending':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Order is still PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					BCO_WC()->api->request_update_payment( $order_id ); // Update order id in Billmate.
					$order->update_status( 'on-hold' );
					break;
				case 'created':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					BCO_WC()->api->request_update_payment( $order_id ); // Update order id in Billmate.
					$order->payment_complete( $process_data['bco_number'] );
					break;
				case 'paid':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $process_data['bco_number'] );
					BCO_WC()->api->request_update_payment( $order_id ); // Update order id in Billmate.
					$order->payment_complete( $process_data['bco_number'] );
					break;
				case 'cancelled':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Order reported Cancelled from Billmate Online. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->update_status( 'cancelled', $note );
					break;
				case 'failed':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Order reported Failed from Billmate Online. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $process_data['bco_number'] ) );
					$order->update_status( 'failed', $note );
					break;
			}
		}
	}
}
