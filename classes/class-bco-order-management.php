<?php
/**
 * Order management class file.
 *
 * @package Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order management class.
 */
class BCO_Order_Management {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'activate_reservation' ) );
	}

	/**
	 * Cancels the order with the payment provider.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function cancel_reservation( $order_id ) {
		$order = wc_get_order( $order_id );
		// If this order wasn't created using aco payment method, bail.
		if ( 'bco' !== $order->get_payment_method() ) {
			return;
		}

		// Check Billmate settings to see if we have the ordermanagement enabled.
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$order_management  = 'yes' === $billmate_settings['order_management'] ? true : false;
		if ( ! $order_management ) {
			return;
		}

		$subscription = $this->check_if_subscription( $order );

		// Check if we have a order number.
		$order_number = get_post_meta( $order_id, '_billmate_order_number', true );
		if ( empty( $purchase_id ) ) {
			$order->add_order_note( __( 'Billmate Checkout reservation could not be cancelled. Missing Billmate order number.', 'billmate-checkout-for-woocommerce' ) );
			$order->set_status( 'on-hold' );
			return;
		}

		// If this reservation was already cancelled, do nothing.
		if ( get_post_meta( $order_id, '_billmate_reservation_cancelled', true ) ) {
			$order->add_order_note( __( 'Could not cancel Billmate Checkout reservation, Billmate Checkout reservation is already cancelled.', 'billmate-checkout-for-woocommerce' ) );
			return;
		}

		// Cancel order.
		$billmate_order = BCO_WC()->api->request_cancel_payment( $order_number );

		// Check if we were successful.
		if ( is_wp_error( $billmate_order ) ) {
			// If error save error message.
			$code          = $billmate_order->get_error_code();
			$message       = $billmate_order->get_error_message();
			$text          = __( 'Billmate API Error on Billmate cancel order: ', 'billmate-checkout-for-woocommerce' ) . '%s %s';
			$formated_text = sprintf( $text, $code, $message );
			$order->add_order_note( $formated_text );
			$order->set_status( 'on-hold' );
		} else {
			// Add time stamp, used to prevent duplicate activations for the same order.
			update_post_meta( $order_id, '_billmate_reservation_cancelled', current_time( 'mysql' ) );
			$order->add_order_note( __( 'Billmate reservation was successfully cancelled.', 'billmate-checkout-for-woocommerce' ) );
		}
	}

	/**
	 * Activate the order with the payment provider.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function activate_reservation( $order_id ) {
		$order = wc_get_order( $order_id );
		// If this order wasn't created using aco payment method, bail.
		if ( 'bco' !== $order->get_payment_method() ) {
			return;
		}

		// Check Billmate settings to see if we have the ordermanagement enabled.
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$order_management  = 'yes' === $billmate_settings['order_management'] ? true : false;
		if ( ! $order_management ) {
			return;
		}

		$subscription = $this->check_if_subscription( $order );
		// If this is a free subscription then stop here.
		if ( $subscription && 0 >= $order->get_total() ) {
			return;
		}

		// Check if we have a order number.
		$order_number = get_post_meta( $order_id, '_billmate_order_number', true );
		if ( empty( $order_number ) ) {
			$order->add_order_note( __( 'Billmate Checkout reservation could not be activated. Missing Billmate order number.', 'billmate-checkout-for-woocommerce' ) );
			$order->set_status( 'on-hold' );
			return;
		}

		// If this reservation was already activated, do nothing.
		if ( get_post_meta( $order_id, '_billmate_reservation_activated', true ) ) {
			$order->add_order_note( __( 'Could not activate Billmate Checkout reservation, Billmate Checkout reservation is already activated.', 'billmate-checkout-for-woocommerce' ) );
			$order->set_status( 'on-hold' );
			return;
		}

		// Activate order.
		$billmate_order = BCO_WC()->api->request_activate_payment( $order_number );
		error_log( 'billmate ordER ' . var_export( $billmate_order, true ) );
		// Check if we were successful.
		if ( is_wp_error( $billmate_order ) ) { // handle error.
			// If error save error message.
			$code          = $billmate_order->get_error_code();
			$message       = $billmate_order->get_error_message();
			$text          = __( 'Billmate API Error on Billmate activate order: ', 'billmate-checkout-for-woocommerce' ) . '%s %s';
			$formated_text = sprintf( $text, $code, $message );
			$order->add_order_note( $formated_text );
			$order->set_status( 'on-hold' );
		} else {
			// Add time stamp, used to prevent duplicate activations for the same order.
			update_post_meta( $order_id, '_billmate_reservation_activated', current_time( 'mysql' ) );
			$order->add_order_note( __( 'Billmate reservation was successfully activated.', 'billmate-checkout-for-woocommerce' ) );
		}

	}

	/**
	 * Checks if the order is a subscription order or not
	 *
	 * @param object $order WC_Order object.
	 * @return boolean
	 */
	public function check_if_subscription( $order ) {
		if ( class_exists( 'WC_Subscriptions_Order' ) && wcs_order_contains_renewal( $order ) ) {
			return true;
		}
		if ( class_exists( 'WC_Subscriptions_Order' ) && wcs_order_contains_subscription( $order ) ) {
			return true;
		}
		return false;
	}
}
