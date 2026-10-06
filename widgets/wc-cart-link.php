<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Cart link — an icon / label linking to the cart, with item count and total. */
class FW_Elementor_Widget_Wc_Cart_Link extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_cart_link';
	}

	public function get_title() {
		return __( 'Cart Link', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-cart-medium';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'cart', 'link', 'count', 'total', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'link', 'label' => __( 'Link', 'fw' ), 'tab' => 'content', 'options' => array( 'icon', 'label', 'show_count', 'show_total', 'hide_when_empty' ) ),
		);
	}
}
