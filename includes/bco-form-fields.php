<?php
/**
 * Settings form fields for the gateway.
 *
 * @package Billmate_Checkout/Includes
 */

$settings = array(
	'enabled'                            => array(
		'title'   => __( 'Enable/Disable', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable ' . $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'default' => 'yes',
	),
	'title'                              => array(
		'title'       => __( 'Title', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => __( $this->method_title, 'billmate-checkout-for-woocommerce' ), // phpcs:ignore
		'desc_tip'    => true,
	),
	'description'                        => array(
		'title'       => __( 'Description', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'textarea',
		'default'     => __( 'Pay with Qvickly via invoice, card and direct bank payments.', 'billmate-checkout-for-woocommerce' ),
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'billmate-checkout-for-woocommerce' ),
	),
	'select_another_method_text'         => array(
		'title'       => __( 'Other payment method button text', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Customize the <em>Select another payment method</em> button text that is displayed in checkout if using other payment methods than Qvickly Checkout. Leave blank to use the default (and translatable) text.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'testmode'                           => array(
		'title'   => __( 'Testmode', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Qvickly Checkout testmode', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'debug'                              => array(
		'title'       => __( 'Debug Log', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log ' . $this->method_title . ' events in <code>%s</code>', 'billmate-checkout-for-woocommerce' ), wc_get_log_file_path( 'billmate_checkout' ) ), // phpcs:ignore
	),
	'checkout_layout'                    => array(
		'title'       => __( 'Checkout layout', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'one_column_checkout' => __( 'One column checkout', 'billmate-checkout-for-woocommerce' ),
			'two_column_checkout' => __( 'Two column checkout', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select the Qvickly Checkout layout.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'two_column_checkout',
		'desc_tip'    => false,
	),
	'company_view'                       => array(
		'title'       => __( 'Checkout mode', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'false' => __( 'Consumer', 'billmate-checkout-for-woocommerce' ),
			'true'  => __( 'Business', 'billmate-checkout-for-woocommerce' ),
		),
		'description' => __( 'Select if you want the checkout to default to B2C or B2B mode.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'false',
		'desc_tip'    => false,
	),
	'show_order_notes'                   => array(
		'title'   => __( 'Show order notes', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Show Order notes field in checkout.', 'billmate-checkout-for-woocommerce' ),
		'default' => 'yes',
	),
	'disable_scroll_to_checkout'         => array(
		'title'   => __( 'Disable scroll to checkout', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Disable automatic scroll to Qvickly checkout iframe when checkout page is rendered.', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'hide_shipping_address'              => array(
		'title'   => __( 'Hide shipping address', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Hide customer shipping address in checkout.', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'logo'                               => array(
		'title'       => __( 'Logo', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Change logotype for the payment. Enter the file name of the logo uploaded in your Qvickly online account. Leave blank to use the standard logo.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'invoice_fee'                        => array(
		'title'       => __( 'Invoice fee', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Add Invoice fee excluding tax. Leave blank to deactivate this feature.', 'billmate-checkout-for-woocommerce' ),
		'default'     => '',
		'desc_tip'    => true,
	),
	'invoice_fee_tax'                    => array(
		'title'       => __( 'Tax class for invoice fee', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'select',
		'options'     => wc_get_product_tax_class_options(),
		'description' => __( 'Select the tax class that should be used for the invoice fee.', 'billmate-checkout-for-woocommerce' ),
		'default'     => 'false',
		'desc_tip'    => false,
	),
	// Monthly cost display.
	'montly_cost_display'                => array(
		'title' => __( 'Monthly cost display', 'billmate-checkout-for-woocommerce' ),
		'type'  => 'title',
	),
	'display_monthly_cost_product_page'  => array(
		'title'   => __( 'Display monthly cost', 'billmate-checkout-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Display monthly cost on single product pages.', 'billmate-checkout-for-woocommerce' ),
		'default' => 'no',
	),
	'monthly_cost_product_page_location' => array(
		'title'   => __( 'Monthly cost placement', 'billmate-checkout-for-woocommerce' ),
		'desc'    => __( 'Select where to display the widget in your product pages', 'billmate-checkout-for-woocommerce' ),
		'id'      => '',
		'default' => '45',
		'type'    => 'select',
		'options' => array(
			'4'  => __( 'Above Title', 'billmate-checkout-for-woocommerce' ),
			'7'  => __( 'Between Title and Price', 'billmate-checkout-for-woocommerce' ),
			'15' => __( 'Between Price and Excerpt', 'billmate-checkout-for-woocommerce' ),
			'25' => __( 'Between Excerpt and Add to cart button', 'billmate-checkout-for-woocommerce' ),
			'35' => __( 'Between Add to cart button and Product meta', 'billmate-checkout-for-woocommerce' ),
			'45' => __( 'Between Product meta and Product sharing buttons', 'billmate-checkout-for-woocommerce' ),
			'55' => __( 'After Product sharing-buttons', 'billmate-checkout-for-woocommerce' ),
		),
	),
	'monthly_cost_text'                  => array(
		'title'       => __( 'Text for monthly cost', 'billmate-checkout-for-woocommerce' ),
		'type'        => 'textarea',
		'default'     => __( '{qvickly_img}<br/>Part pay from {qvickly_price}/month', 'billmate-checkout-for-woocommerce' ),
		'desc_tip'    => false,
		'description' => __( 'Use {qvickly_img} to display the Qvickly logo and {qvickly_price} to display the monthly fee as a formatted WooCommerce price (with currency).', 'billmate-checkout-for-woocommerce' ),
	),
	// SE.
	'credentials_se'                     => array(
		'title' => __( 'API Credentials Sweden', 'billmate-checkout-for-woocommerce' ),
		'type'  => 'title',
	),
	'merchant_id_se'                     => array(
		'title'    => __( 'Client ID', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'text',
		'default'  => '',
		'desc_tip' => true,
	),
	'api_key_se'                         => array(
		'title'    => __( 'Client Secret', 'billmate-checkout-for-woocommerce' ),
		'type'     => 'password',
		'default'  => '',
		'desc_tip' => true,
	),
);
return apply_filters( 'bco_settings', $settings );
