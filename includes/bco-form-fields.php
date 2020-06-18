<?php
/**
 * Settings form fields for the gateway.
 *
 * @package Billmate_Checkout/Includes
 */

$settings = array(
	'enabled'                    => array(
		'title'   => __( 'Enable/Disable', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable ' . $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'default' => 'yes',
	),
	'title'                      => array(
		'title'       => __( 'Title', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => __( $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'desc_tip'    => true,
	),
	'description'                => array(
		'title'       => __( 'Description', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'textarea',
		'default'     => __( 'Pay with Billmate via invoice, card and direct bank payments.', 'billmate-checkout-for-woocommerce' ),
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
	),
	'select_another_method_text' => array(
		'title'       => __( 'Other payment method button text', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Customize the <em>Select another payment method</em> button text that is displayed in checkout if using other payment methods than Billmate Checkout. Leave blank to use the default (and translatable) text.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'testmode'                   => array(
		'title'   => __( 'Testmode', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Billmate Checkout testmode', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'debug'                      => array(
		'title'       => __( 'Debug Log', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'billmate-checkout-for-woocommerce' ), wc_get_log_file_path( 'billmate_checkout' ) ), // phpcs:ignore
	),
	'checkout_flow'              => array(
		'title'       => __( 'Checkout flow', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'checkout'      => __( 'Checkout', 'billmate-checkout-for-woocommerce' ),
			'pay_for_order' => __( 'Pay for Order', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select how Billmate should be integrated in WooCommerce. <strong>Checkout</strong> – the checkout is embedded in the WooCommerce checkout page and partially replaces the checkout form. <strong>Pay for Order</strong> – the customer is redirected to the WooCommerce "Pay for Order" page.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'pay_for_order',
		'desc_tip'    => false,
	),
	'checkout_layout'            => array(
		'title'       => __( 'Billmate checkout layout', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'one_column_checkout' => __( 'One column checkout', 'billmate-checkout-for-woocommerce' ),
			'two_column_checkout' => __( 'Two column checkout', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select the Billmate checkout layout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'two_column_checkout',
		'desc_tip'    => false,
	),
	// SE.
	'credentials_se'             => array(
		'title' => 'API Credentials Sweden',
		'type'  => 'title',
	),
	'merchant_id_se'             => array(
		'title'    => __( 'Client ID', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'text',
		'default'  => '',
		'desc_tip' => true,
	),
	'api_key_se'                 => array(
		'title'    => __( 'Client Secret', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'text',
		'default'  => '',
		'desc_tip' => true,
	),
);
return apply_filters( 'bco_settings', $settings );
