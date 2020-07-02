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
	}

	/**
	 * Push callback function.
	 */
	public function push_cb() {
		$post           = file_get_contents( 'php://input' );
		$data           = json_decode( $post, true );
		$order_id       = $data['data']['orderid'];
		$transaction_id = $data['data']['number'];
		$bco_status     = strtolower( $data['data']['status'] );
		$order          = wc_get_order( $order_id );

		if ( is_object( $order ) && ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			switch ( $bco_status ) {
				case 'pending':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Order is still PENDING APPROVAL by Billmate. Please visit Billmate Online for the latest status on this order. Billmate Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $transaction_id ) );
					$order->add_order_note( $note );
					update_post_meta( $order_id, '_billmate_transaction_id', $transaction_id );
					$order->update_status( 'on-hold' );
					break;
				case 'created':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $transaction_id ) );
					$order->add_order_note( $note );
					$order->payment_complete( $transaction_id );
					update_post_meta( $order_id, '_billmate_transaction_id', $transaction_id );
					break;
				case 'paid':
					// Translators: Billmate transaction id.
					$note = sprintf( __( 'Payment via Billmate Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $transaction_id ) );
					$order->add_order_note( $note );
					$order->payment_complete( $transaction_id );
					update_post_meta( $order_id, '_billmate_transaction_id', $transaction_id );
					break;
				case 'cancelled':
					break;
				case 'failed':
					break;
			}
		}
	}
}
