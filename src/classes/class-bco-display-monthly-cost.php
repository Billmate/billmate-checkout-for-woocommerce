<?php
/**
 * Display payment plans class for Qvickly checkout.
 *
 * @package  Billmate_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * BCO_Templates class.
 */
class BCO_Display_Monthly_Cost {

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var $instance
	 */
	protected static $instance;

    public $enabled;
    public $display_monthly_cost_product_page;
    public $monthly_cost_text;
    public $monthly_cost_product_page_location;

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
		$bco_settings                             = get_option( 'woocommerce_bco_settings' );
		$this->enabled                            = ( isset( $bco_settings['enabled'] ) ) ? $bco_settings['enabled'] : '';
		$this->display_monthly_cost_product_page  = ( isset( $bco_settings['display_monthly_cost_product_page'] ) ) ? $bco_settings['display_monthly_cost_product_page'] : '';
		$this->monthly_cost_text                  = ( isset( $bco_settings['monthly_cost_text'] ) ) ? $bco_settings['monthly_cost_text'] : '';
		$this->monthly_cost_product_page_location = ( isset( $bco_settings['monthly_cost_product_page_location'] ) ) ? $bco_settings['monthly_cost_product_page_location'] : '45';

		add_action( 'template_redirect', array( $this, 'init_class' ) );
	}

	/**
	 * Initiates the class
	 *
	 * @return void
	 */
	public function init_class() {
		if ( 'yes' === $this->enabled && 'yes' === $this->display_monthly_cost_product_page && is_product() ) {

			$target   = apply_filters( 'bco_display_payment_plans_product_target', 'woocommerce_single_product_summary' );
			$priority = apply_filters( 'bco_display_payment_plans_product_priority', $this->monthly_cost_product_page_location );
			add_action( $target, array( $this, 'get_part_payment_data' ), $priority );
		}
	}

	/**
	 * Makes the payment plan request and adds the monthly cost data to the page (if we get a payment plan in the response).
	 *
	 * @return void
	 */
	public function get_part_payment_data() {
		global $product;
		if ( $product->is_type( 'variable' ) ) {
			$price = $product->get_variation_price( 'min' ) * 100;
		} else {
			$price = wc_get_price_to_display( $product ) * 100;
		}

		$payment_plans = BCO_WC()->api->request_get_payment_plans( $price );
		if ( ! is_wp_error( $payment_plans ) ) {
			$this->format_and_render_part_payment_data( $payment_plans );
		}
	}

	/**
	 * Formats and prints the part payment widget html.
	 *
	 * @param array $payment_plans Payment plans fetched from Qvickly.
	 *
	 * @return void
	 */
	public function format_and_render_part_payment_data( $payment_plans ) {
		if ( isset( $payment_plans['data'] ) ) {
			$min_monthly_cost = min( array_column( $payment_plans['data'], 'monthlycost' ) );
			$monthly_cost     = wc_price( $min_monthly_cost / 100 );
			$bco_image_src    = apply_filters( 'bco_monthly_cost_image_src', BILLMATE_CHECKOUT_URL . '/assets/images/qvickly-logo.png' );
			$bco_image_width  = '145';
			$bco_image_html   = '<img src="' . $bco_image_src . '" alt="Qvickly logo" style="max-width:' . $bco_image_width . 'px"/>';
			$bco_image_html   = apply_filters( 'bco_monthly_cost_image_html', $bco_image_html );

			$replacements = array(
				'{billmate_img}'   => $bco_image_html,
				'{billmate_price}' => $monthly_cost,
				'{qvickly_img}'    => $bco_image_html,
				'{qvickly_price}'  => $monthly_cost,
			);
			$content      = str_replace( array_keys( $replacements ), $replacements, $this->monthly_cost_text );

			echo '<div class="billmate-product-monthly-cost">' . wp_kses_post( $content ) . '</div>';
		}

	}

}

BCO_Display_Monthly_Cost::get_instance();
