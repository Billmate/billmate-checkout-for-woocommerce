<?php
/**
 * Helper class for token.
 *
 * @package Framework\Helpers\WooCommerce\Payment
 * @author Krokedil
 */

/**
 * Class Krokedil_Payment_Token
 */
class Krokedil_Payment_Token extends WC_Payment_Token {

	/**
	 * Token Type
	 *
	 * @var string
	 */
	protected $type = 'stub';

	/**
	 * Retruns extra
	 *
	 * @return mixed
	 */
	public function get_extra() {
		return $this->get_meta( 'extra' );
	}

	/**
	 * Set extra
	 *
	 * @param string|array $extra meta value.
	 */
	public function set_extra( $extra ) {
		$this->add_meta_data( 'extra', $extra, true );
	}
}
