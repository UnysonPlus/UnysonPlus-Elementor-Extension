<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** My account — WooCommerce's account area (orders, addresses, details). */
class FW_Elementor_Widget_Wc_My_Account extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_my_account';
	}

	public function get_title() {
		return __( 'My Account', 'fw' );
	}

	public function get_icon() {
		return 'eicon-my-account';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'my account', 'orders', 'woocommerce' );
	}

	protected function sections() {
		return array();
	}

	protected function no_options_note() {
		return __( 'This element has no settings of its own; it follows the WooCommerce settings. Use the Advanced tab for spacing and visibility.', 'fw' );
	}
}
