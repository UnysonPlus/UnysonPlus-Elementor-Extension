<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Free shipping bar — how much more the visitor needs to spend for free shipping. */
class FW_Elementor_Widget_Wc_Free_Shipping extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_free_shipping';
	}

	public function get_title() {
		return __( 'Free Shipping Bar', 'fw' );
	}

	public function get_icon() {
		return 'eicon-progress-tracker';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'free shipping', 'progress', 'cart', 'woocommerce' );
	}

	protected function sections() {
		return array();
	}

	protected function no_options_note() {
		return __( 'This element has no settings of its own; it follows the WooCommerce settings. Use the Advanced tab for spacing and visibility.', 'fw' );
	}
}
