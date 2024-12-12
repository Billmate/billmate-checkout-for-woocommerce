<?php
/**
 * Templates class for Qvickly checkout.
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

	public $enabled;
	public $show_order_notes;
	public $checkout_layout;

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
		$bco_settings           = get_option( 'woocommerce_bco_settings' );
		$this->enabled          = ( isset( $bco_settings['enabled'] ) ) ? $bco_settings['enabled'] : '';
		$this->show_order_notes = ( isset( $bco_settings['show_order_notes'] ) ) ? $bco_settings['show_order_notes'] : 'yes';
		$checkout_flow          = ( isset( $bco_settings['checkout_flow'] ) ) ? $bco_settings['checkout_flow'] : 'checkout';
		$this->checkout_layout  = ( isset( $bco_settings['checkout_layout'] ) ) ? $bco_settings['checkout_layout'] : 'two_column_checkout';

		if ( 'checkout' === $checkout_flow ) {
			// Override template if Qvickly Checkout page.
			add_filter( 'wc_get_template', array( $this, 'override_checkout_template' ), 999, 2 );
		} else {
			// Override template if Qvickly pay for order page.
			add_filter( 'wc_get_template', array( $this, 'override_pay_template' ), 999, 2 );
		}

		// Template hooks.
		add_action( 'bco_wc_after_wrapper', array( $this, 'add_wc_form' ), 10 );
		add_action( 'bco_wc_after_order_review', array( $this, 'add_extra_checkout_fields' ), 10 );
		add_action( 'bco_wc_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
		add_action( 'bco_wc_before_checkout_form', 'woocommerce_checkout_coupon_form', 20 );
		add_action( 'bco_wc_after_order_review', 'bco_wc_show_another_gateway_button', 20 );

		// Hook to check if we should hide the Order notes field in checkout.
		add_action( 'template_redirect', array( $this, 'maybe_hide_order_notes_field' ), 1000 );

		// Body class. For checkout layout setting.
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
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
			$confirm = filter_input( INPUT_GET, 'confirm', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			// Qvickly Pay for Order.
			if ( 'checkout/form-pay.php' === $template_name ) {
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( locate_template( 'woocommerce/billmate-pay.php' ) ) {
					$billmate_pay_template = locate_template( 'woocommerce/billmate-pay.php' );
				} else {
					$billmate_pay_template = BILLMATE_CHECKOUT_PATH . '/templates/billmate-pay.php';
				}

				// Qvickly pay for order page.
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
	 * Override checkout form template if Qvickly Checkout is the selected payment method.
	 *
	 * @param string $template      Template.
	 * @param string $template_name Template name.
	 *
	 * @return string
	 */
	public function override_checkout_template( $template, $template_name ) {
		if ( is_checkout() ) {
			$confirm = filter_input( INPUT_GET, 'confirm', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			// Don't display BCO template if we have a cart that doesn't needs payment.
			if ( ! WC()->cart->needs_payment() ) {
				return $template;
			}

			// Qvickly Checkout.
			if ( 'checkout/form-checkout.php' === $template_name ) {
				$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

				if ( locate_template( 'woocommerce/billmate-checkout.php' ) ) {
					$billmate_checkout_template = locate_template( 'woocommerce/billmate-checkout.php' );
				} else {
					$billmate_checkout_template = BILLMATE_CHECKOUT_PATH . '/templates/billmate-checkout.php';
				}

				// Qvickly checkout page.
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

	/**
	 * Maybe hide the Customer order notes field in checkout.
	 *
	 * @return void
	 */
	public function maybe_hide_order_notes_field() {

		if ( is_checkout() && 'yes' === $this->enabled && 'yes' !== $this->show_order_notes && method_exists( WC()->session, 'get' ) ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();

			if ( 'bco' === WC()->session->get( 'chosen_payment_method' ) ) {
				// If Qvickly Checkout is the selected payment gateway.
				add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
			} elseif ( null === WC()->session->get( 'chosen_payment_method' ) || '' === WC()->session->get( 'chosen_payment_method' ) ) {
				// If no payment gatewy have been selected but Qvickly Checkout is the default one.
				reset( $available_gateways );
				if ( 'bco' === key( $available_gateways ) ) {
					add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
				}
			}
		}
	}

	/**
	 * Add bco-two-column-checkout body class.
	 *
	 * @param array $class CSS classes used in body tag.
	 *
	 * @return array
	 */
	public function add_body_class( $class ) {
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
			// Don't display Qvickly body classes if we have a cart that doesn't needs payment.
			if ( method_exists( WC()->cart, 'needs_payment' ) && ! WC()->cart->needs_payment() ) {
				return $class;
			}

			$first_gateway = '';
			if ( WC()->session->get( 'chosen_payment_method' ) ) {
				$first_gateway = WC()->session->get( 'chosen_payment_method' );
			} else {
				$available_payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
				reset( $available_payment_gateways );
				$first_gateway = key( $available_payment_gateways );
			}

			if ( 'bco' === $first_gateway && 'two_column_checkout' === $this->checkout_layout ) {
				$class[] = 'bco-two-column-checkout';
			}
		}
		return $class;
	}
}

BCO_Templates::get_instance();
