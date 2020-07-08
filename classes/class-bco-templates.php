<?php
/**
 * Templates class for Billmate checkout.
 *
 * @package  Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * BCO_Templates class.
 */
class BCO_Templates {

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
	 * Plugin actions.
	 */
	public function __construct() {
		$bco_settings  = get_option( 'woocommerce_bco_settings' );
		$checkout_flow = ( isset( $bco_settings['checkout_flow'] ) ) ? $bco_settings['checkout_flow'] : 'checkout';
		if ( 'checkout' === $checkout_flow ) {
			// Override template if Billmate Checkout page.
			add_filter( 'wc_get_template', array( $this, 'override_checkout_template' ), 999, 2 );
		} else {
			// Override template if Billmate pay for order page.
			add_filter( 'wc_get_template', array( $this, 'override_pay_template' ), 999, 2 );
		}

		// Template hooks.
		add_action( 'bco_wc_after_wrapper', array( $this, 'add_wc_form' ), 10 );
		add_action( 'bco_wc_after_order_review', array( $this, 'add_extra_checkout_fields' ), 10 );
		add_action( 'bco_wc_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		add_action( 'bco_wc_before_checkout_form', 'woocommerce_checkout_coupon_form', 20 );
		add_action( 'bco_wc_after_order_review', 'bco_wc_show_another_gateway_button', 20 );
	}

	/**
	 * Override pay for order template.
	 *
	 * @param string $template      Template.
	 * @param string $template_name Template name.
	 *
	 * @return string
	 */
	public function override_pay_template( $template, $template_name ) {
		if ( is_checkout() ) {
			$confirm = filter_input( INPUT_GET, 'confirm', FILTER_SANITIZE_STRING );
			// Billmate Pay for Order.
			if ( 'checkout/form-pay.php' === $template_name ) {
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( locate_template( 'woocommerce/billmate-pay.php' ) ) {
					$billmate_pay_template = locate_template( 'woocommerce/billmate-pay.php' );
				} else {
					$billmate_pay_template = BILLMATE_CHECKOUT_PATH . '/templates/billmate-pay.php';
				}

				// Billmate pay for order page.
				if ( array_key_exists( 'bco', $available_gateways ) ) {
					// If chosen payment method exists.
					if ( 'bco' === WC()->session->get( 'chosen_payment_method' ) ) {
						if ( empty( $confirm ) ) {
							$template = $billmate_pay_template;
						}
					}

					// If chosen payment method does not exist and BCO is the first gateway.
					if ( null === WC()->session->get( 'chosen_payment_method' ) || '' === WC()->session->get( 'chosen_payment_method' ) ) {
						reset( $available_gateways );

						if ( 'bco' === key( $available_gateways ) ) {
							if ( empty( $confirm ) ) {
								$template = $billmate_pay_template;
							}
						}
					}

					// If another gateway is saved in session, but has since become unavailable.
					if ( WC()->session->get( 'chosen_payment_method' ) ) {
						if ( ! array_key_exists( WC()->session->get( 'chosen_payment_method' ), $available_gateways ) ) {
							reset( $available_gateways );

							if ( 'bco' === key( $available_gateways ) ) {
								if ( empty( $confirm ) ) {
									$template = $billmate_pay_template;
								}
							}
						}
					}
				}
			}
		}

		return $template;
	}

	/**
	 * Override checkout form template if Billmate Checkout is the selected payment method.
	 *
	 * @param string $template      Template.
	 * @param string $template_name Template name.
	 *
	 * @return string
	 */
	public function override_checkout_template( $template, $template_name ) {
		if ( is_checkout() ) {
			$confirm = filter_input( INPUT_GET, 'confirm', FILTER_SANITIZE_STRING );
			// Don't display BCO template if we have a cart that doesn't needs payment.
			if ( ! WC()->cart->needs_payment() ) {
				return $template;
			}

			// Billmate Checkout.
			if ( 'checkout/form-checkout.php' === $template_name ) {
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( locate_template( 'woocommerce/billmate-checkout.php' ) ) {
					$billmate_checkout_template = locate_template( 'woocommerce/billmate-checkout.php' );
				} else {
					$billmate_checkout_template = BILLMATE_CHECKOUT_PATH . '/templates/billmate-checkout.php';
				}

				// Billmate checkout page.
				if ( array_key_exists( 'bco', $available_gateways ) ) {
					// If chosen payment method exists.
					if ( 'bco' === WC()->session->get( 'chosen_payment_method' ) ) {
						if ( empty( $confirm ) ) {
							$template = $billmate_checkout_template;
						}
					}

					// If chosen payment method does not exist and BCO is the first gateway.
					if ( null === WC()->session->get( 'chosen_payment_method' ) || '' === WC()->session->get( 'chosen_payment_method' ) ) {
						reset( $available_gateways );

						if ( 'bco' === key( $available_gateways ) ) {
							if ( empty( $confirm ) ) {
								$template = $billmate_checkout_template;
							}
						}
					}

					// If another gateway is saved in session, but has since become unavailable.
					if ( WC()->session->get( 'chosen_payment_method' ) ) {
						if ( ! array_key_exists( WC()->session->get( 'chosen_payment_method' ), $available_gateways ) ) {
							reset( $available_gateways );

							if ( 'bco' === key( $available_gateways ) ) {
								if ( empty( $confirm ) ) {
									$template = $billmate_checkout_template;
								}
							}
						}
					}
				}
			}
		}

		return $template;
	}

	/**
	 * Adds the WC form and other fields to the checkout page.
	 *
	 * @return void
	 */
	public function add_wc_form() {
		?>
		<div aria-hidden="true" id="bco-wc-form" style="position:absolute; top:-99999px; left:-99999px;">
			<?php do_action( 'woocommerce_checkout_billing' ); ?>
			<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			<div id="bco-nonce-wrapper">
				<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
			</div>
			<input id="payment_method_bco" type="radio" class="input-radio" name="payment_method" value="bco" checked="checked" />		</div>
		<?php
	}

	/**
	 * Adds the extra checkout field div to the checkout page.
	 *
	 * @return void
	 */
	public function add_extra_checkout_fields() {
		do_action( 'bco_wc_before_extra_fields' );
		?>
		<div id="bco-extra-checkout-fields">
		</div>
		<?php
		do_action( 'bco_wc_after_extra_fields' );
	}
}

BCO_Templates::get_instance();
