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
		$post       = file_get_contents( 'php://input' );
		$order_id   = $post['data']['orderid'];
		$bco_status = strtolower( $post['data']['status'] );
		$order      = wc_get_order( $order_id );

		if ( ! $order->has_status( array( 'processing', 'completed' ) ) ) {
			if ( 'created' === $bco_status ) {
				$order->payment_complete( $klarna_order_id );
				// translators: Billmate order ID.
				$note = sprintf( __( 'Payment via Billmate Checkout, order ID: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $order_id ) );
				$order->add_order_note( $note );
			} elseif ( 'pending' === $bco_status ) {
				// translators: Billmate order ID.
				$note = sprintf( __( 'Billmate order is under review, order ID: %s.', 'billmate-checkout-for-woocommerce' ), sanitize_key( $order_id ) );
				$order->update_status( 'on-hold', $note );
			}
		}
	}
}
