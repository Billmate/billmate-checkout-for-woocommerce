<?php
/**
 * Functions file for the plugin.
 *
 * @package  Billmate_Checkout/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Qvickly Checkout iframe.
 *
 * @return void
 */
function bco_show_iframe() {
	$billmate_order = bco_init_checkout();
	do_action( 'bco_show_iframe', $billmate_order );
	echo '<iframe name="checkout_iframe" id="checkout" src="' . WC()->session->get( 'bco_wc_checkout_url' ) . '" sandbox="allow-same-origin allow-scripts allow-modals allow-popups allow-forms allow-top-navigation" style="width:100%;min-height:800px;border:none;" scrolling="no"></iframe>'; // phpcs:ignore
}


/**
 * Init checkout.
 *
 * @return void
 */
function bco_init_checkout() {
	$bco_settings  = get_option( 'woocommerce_bco_settings' );
	$checkout_flow = ( isset( $bco_settings['checkout_flow'] ) ) ? $bco_settings['checkout_flow'] : 'checkout';

	if ( 'checkout' === $checkout_flow ) { // Only calculate cart when checkout flow is checkout. No cart calculation needed for Pay for Order flow.
		// Need to calculate these here, because WooCommerce hasn't done it yet.
		WC()->cart->calculate_fees();
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();
		if ( WC()->session->get( 'bco_wc_hash' ) && get_woocommerce_currency() === WC()->session->get( 'bco_currency' ) ) {

			// Try to update the order, if it fails try to create new order.
			$billmate_order = BCO_WC()->api->request_update_checkout( WC()->session->get( 'bco_wc_number' ) );
			if ( ! $billmate_order ) {
				// If update order failed try to create new order.
				WC()->session->set( 'bco_wc_temp_order_id', 'tmp' . md5( uniqid( wp_rand(), true ) ) );
				$billmate_order = BCO_WC()->api->request_init_checkout();
				if ( ! $billmate_order ) {
					// If failed then bail.
					return;
				}
				WC()->session->set( 'bco_wc_number', $billmate_order['data']['number'] );
				WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
				WC()->session->set( 'bco_currency', get_woocommerce_currency() );
				set_checkout_hash( $billmate_order['data']['url'] );
				return $billmate_order;
			}
			WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
			set_checkout_hash( $billmate_order['data']['url'] );
			return $billmate_order;

		} else {
			// Initialize payment.
			WC()->session->set( 'bco_wc_temp_order_id', 'tmp' . md5( uniqid( wp_rand(), true ) ) );
			$billmate_order = BCO_WC()->api->request_init_checkout();
			if ( ! $billmate_order ) {
				return;
			}
			WC()->session->set( 'bco_wc_number', $billmate_order['data']['number'] );
			WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] . '?activateJsEvents=1' );
			WC()->session->set( 'bco_currency', get_woocommerce_currency() );
			set_checkout_hash( $billmate_order['data']['url'] );

			return $billmate_order;
		}
	} else { // If Checkout flow is Pay for Order then we have access to order id.
		global $wp;
		$order_id = $wp->query_vars['order-pay'];
		// Initialize payment.
		$billmate_order = BCO_WC()->api->request_init_checkout( $order_id );
		if ( ! $billmate_order ) {
			return;
		}

		WC()->session->set( 'bco_wc_order_id', $billmate_order['data']['orderid'] );
		WC()->session->set( 'bco_wc_checkout_url', $billmate_order['data']['url'] );
		WC()->session->set( 'bco_currency', get_woocommerce_currency() );
		set_checkout_hash( $billmate_order['data']['url'] );

		return $billmate_order;
	}

}

/**
 * Set checkout hash.
 *
 * @param string $url The checkout url.
 * @return void
 */
function set_checkout_hash( $url ) {
	// Extract the hash from the Qvickly checkout url.
	$parts = explode( '/', $url );
	$sum   = count( $parts );
	$hash  = ( 'test' === $parts[ $sum - 1 ] ) ? str_replace( '\\', '', $parts[ $sum - 2 ] ) : str_replace( '\\', '', $parts[ $sum - 1 ] );
	// Set chekout hash as session variable.
	WC()->session->set( 'bco_wc_hash', $hash );
}

/**
 * Shows select another payment method button in Qvickly Checkout page.
 */
function bco_wc_show_another_gateway_button() {
	$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

	if ( count( $available_gateways ) > 1 ) {
		$settings                   = get_option( 'woocommerce_bco_settings' );
		$select_another_method_text = isset( $settings['select_another_method_text'] ) && '' !== $settings['select_another_method_text'] ? $settings['select_another_method_text'] : __( 'Select another payment method', 'billmate-checkout-for-woocommerce' );

		?>
		<p class="billmate-checkout-select-other-wrapper">
			<a class="checkout-button button" href="#" id="billmate-checkout-select-other">
				<?php echo esc_html( $select_another_method_text ); ?>
			</a>
		</p>
		<?php
	}
}

/**
 * Set Qvickly Checkout payment method tile.
 *
 * @param string $order_id The WooCommerce order id.
 * @param array  $bco_order The Qvickly order.
 * @return void
 */
function bco_set_payment_method_title( $order_id, $bco_order = array() ) {
	$bco_payment_method      = '';
	$bco_payment_method_name = '';
	if ( isset( $bco_order['data']['PaymentData']['method'] ) ) {
		$bco_payment_method = $bco_order['data']['PaymentData']['method'];
		update_post_meta( $order_id, '_billmate_payment_method_id', $bco_payment_method );
	}

	if ( isset( $bco_order['data']['PaymentData']['method_name'] ) ) {
		$bco_payment_method_name = $bco_order['data']['PaymentData']['method_name'];
		update_post_meta( $order_id, '_billmate_payment_method_name', $bco_payment_method_name );
	}

	$bco_settings   = get_option( 'woocommerce_bco_settings' );
	$settings_title = ( isset( $bco_settings['title'] ) ) ? $bco_settings['title'] : '';
	$method_title   = $settings_title . ' ' . $bco_payment_method_name;
	$order          = wc_get_order( $order_id );
	$order->set_payment_method_title( $method_title );
	$order->save();
}


/**
 * Unsets the sessions used by the plguin.
 *
 * @return void
 */
function bco_wc_unset_sessions() {
	WC()->session->__unset( 'bco_wc_order_id' );
	WC()->session->__unset( 'bco_wc_hash' );
	WC()->session->__unset( 'bco_wc_checkout_url' );
	WC()->session->__unset( 'bco_wc_number' );
	WC()->session->__unset( 'bco_wc_temp_order_id' );
	WC()->session->__unset( 'bco_currency' );
}


/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function bco_extract_error_message( $wp_error ) {
	wc_print_notice( $wp_error->get_error_message(), 'error' );
}


/**
 * Confirm Qvickly order.
 *
 * @param string $order_id The WooCommerce order id.
 * @param array  $bco_checkout The Qvickly checkout data.
 */
function bco_confirm_billmate_order( $order_id, $bco_checkout ) {

	$order = wc_get_order( $order_id );
	if ( is_object( $order ) && ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {

		BCO_Logger::log( 'Trigger bco_confirm_billmate_order for order_id: ' . $order_id );

		$bco_order_number = $bco_checkout['data']['PaymentData']['order']['number'];
		update_post_meta( $order_id, '_transaction_id', $bco_order_number );

		// Make get_payment request if we have Qvickly order number.
		if ( '' !== $bco_order_number ) {
			$bco_order = BCO_WC()->api->request_get_payment( $bco_order_number );
		}

		// Confirm order.
		switch ( strtolower( $bco_checkout['data']['PaymentData']['order']['status'] ) ) {
			case 'pending':
				// Translators: Qvickly transaction id.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Qvickly. Please visit Qvickly Online for the latest status on this order. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_order_number ) );
				$order->add_order_note( $note );
				$order->update_status( 'on-hold' );

				break;
			case 'created':
				bco_payment_complete( $order, $bco_order_number );
				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				break;
			case 'paid':
				bco_payment_complete( $order, $bco_order_number );
				do_action( 'bco_wc_payment_complete', $order_id, $bco_checkout );
				break;
			case 'cancelled':
				break;
			case 'failed':
				break;
		}
	}

}


/**
 * Confirm Qvickly redirect order.
 *
 * @param string   $order_id The WooCommerce order id.
 * @param WC_Order $order The WooCommerce order.
 * @param array    $data The content data from Qvickly redirect.
 * @return void
 */
function bco_confirm_billmate_redirect_order( $order_id, $order, $data ) {
	$bco_transaction_id = get_post_meta( $order_id, '_billmate_transaction_id', true );
	update_post_meta( $order_id, '_transaction_id', $bco_transaction_id );

	if ( is_object( $order ) && ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {

		BCO_Logger::log( 'Trigger bco_confirm_billmate_redirect_order for order_id: ' . $order_id );

		switch ( strtolower( $data['status'] ) ) {
			case 'pending':
				// Translators: Qvickly transaction id.
				$note = sprintf( __( 'Order is PENDING APPROVAL by Qvickly. Please visit Qvickly Online for the latest status on this order. Qvickly Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_transaction_id ) );
				$order->add_order_note( $note );
				$order->update_status( 'on-hold' );

				break;
			case 'created':
				bco_payment_complete( $order, $bco_transaction_id );
				do_action( 'bco_wc_payment_complete', $order_id, $data );
				break;
			case 'paid':
				bco_payment_complete( $order, $bco_transaction_id );
				do_action( 'bco_wc_payment_complete', $order_id, $data );
				break;
			case 'cancelled':
				break;
			case 'failed':
				break;
		}
	}
}

/**
 * Finds an Order ID based on a temp order id set in Qvickly create request.
 *
 * @param string $billmate_temp_order_id A temporary order id set in create request sent to Qvickly.
 * @return int The ID of an order, or 0 if the order could not be found.
 */
function bco_get_order_id_by_temp_order_id( $billmate_temp_order_id ) {
	$query_args = array(
		'fields'      => 'ids',
		'post_type'   => wc_get_order_types(),
		'post_status' => array_keys( wc_get_order_statuses() ),
		'meta_key'    => '_billmate_temp_order_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'meta_value'  => sanitize_text_field( wp_unslash( $billmate_temp_order_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'date_query'  => array(
			array(
				'after' => '2 day ago',
			),
		),
	);

	$orders = get_posts( $query_args );

	if ( $orders ) {
		$order_id = $orders[0];
	} else {
		$order_id = 0;
	}

	return $order_id;
}

/**
 * Finds an Order ID based on a transaction ID (the Qvickly invoice number).
 *
 * @param string $transaction_id Qvickly invoice number saved as Transaction ID in WC order.
 * @return int The ID of an order, or 0 if the order could not be found.
 */
function bco_get_order_id_by_transaction_id( $transaction_id ) {
	$query_args = array(
		'fields'      => 'ids',
		'post_type'   => wc_get_order_types(),
		'post_status' => array_keys( wc_get_order_statuses() ),
		'meta_key'    => '_transaction_id', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'meta_value'  => sanitize_text_field( wp_unslash( $transaction_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'date_query'  => array(
			array(
				'after' => '30 day ago',
			),
		),
	);

	$orders = get_posts( $query_args );

	if ( $orders ) {
		$order_id = $orders[0];
	} else {
		$order_id = 0;
	}

	return $order_id;
}

/**
 * Finds an Order ID based on a transaction ID (the Qvickly invoice number).
 *
 * @param string $billmate_orderid Qvickly orderid _billmate_saved_woo_order_no in WC order ($order->get_order_number).
 * @return int The ID of an order, or 0 if the order could not be found.
 */
function bco_get_order_id_by_billmate_saved_woo_order_no( $billmate_orderid ) {
	$query_args = array(
		'fields'      => 'ids',
		'post_type'   => wc_get_order_types(),
		'post_status' => array_keys( wc_get_order_statuses() ),
		'meta_key'    => '_billmate_saved_woo_order_no', // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'meta_value'  => sanitize_text_field( wp_unslash( $billmate_orderid ) ), // phpcs:ignore WordPress.DB.SlowDBQuery -- Slow DB Query is ok here, we need to limit to our meta key.
		'date_query'  => array(
			array(
				'after' => '2 day ago',
			),
		),
	);

	$orders = get_posts( $query_args );

	if ( $orders ) {
		$order_id = $orders[0];
	} else {
		$order_id = 0;
	}

	return $order_id;
}

/**
 * Adds the invoice fee to WC order if this is a invoice payment and invoice fee is set in plugin settings.
 *
 * @param object $order WooCommerce order.
 * @return void.
 */
function bco_maybe_add_invoice_fee( $order ) {
	// Add invoice fee to order.
	$order_id = $order->get_id();
	if ( '1' === get_post_meta( $order_id, '_billmate_payment_method_id', true ) ) {
		$billmate_settings = get_option( 'woocommerce_bco_settings' );
		$invoice_fee       = isset( $billmate_settings['invoice_fee'] ) ? str_replace( ',', '.', $billmate_settings['invoice_fee'] ) : '';

		// Check that we have an invoice fee and that no transaction id is set yet (to avoid that we add invoice fee multiple times).
		if ( ! empty( $invoice_fee ) && is_numeric( $invoice_fee ) && empty( $order->get_transaction_id() ) ) {

			$fee = new WC_Order_Item_Fee();

			$fee_args = array(
				'name'      => __( 'Invoice fee', 'billmate-checkout-for-woocommerce' ),
				'total'     => $invoice_fee,
				'tax_class' => $billmate_settings['invoice_fee_tax'],
			);

			$fee->set_props( $fee_args );
			$order->add_item( $fee );
			$order->calculate_totals();
			$order->save();
		}
	}
}

/**
 * Adds order note to order, trigger payment complete and removes _billmate_confirm_started metadata.
 *
 * @param object $order WooCommerce order.
 * @param string $bco_transaction_id Qvickly transaction id.
 * @return void.
 */
function bco_payment_complete( $order, $bco_transaction_id ) {
	// Translators: Qvickly transaction id.
	$note = sprintf( __( 'Payment via Qvickly Checkout. Transaction id: %s', 'billmate-checkout-for-woocommerce' ), sanitize_key( $bco_transaction_id ) );
	$order->add_order_note( $note );
	$order->payment_complete( $bco_transaction_id );
	$order->delete_meta_data( '_billmate_confirm_started' );
	$order->save();
}
