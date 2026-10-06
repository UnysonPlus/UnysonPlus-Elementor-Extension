<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Cart — WooCommerce's cart, styled by Unyson+. */
class FW_Elementor_Widget_Wc_Cart extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_cart';
	}

	public function get_title() {
		return __( 'Cart', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-cart-solid';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'cart', 'basket', 'woocommerce' );
	}

	protected function sections() {
		return array();
	}

	protected function no_options_note() {
		return __( 'This element has no settings of its own; it follows the WooCommerce settings. Use the Advanced tab for spacing and visibility.', 'fw' );
	}
}
