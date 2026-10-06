<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Order tracking — the order-lookup form. */
class FW_Elementor_Widget_Wc_Order_Tracking extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_order_tracking';
	}

	public function get_title() {
		return __( 'Order Tracking', 'fw' );
	}

	public function get_icon() {
		return 'eicon-search-results';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'order', 'tracking', 'woocommerce' );
	}

	protected function sections() {
		return array();
	}

	protected function no_options_note() {
		return __( 'This element has no settings of its own; it follows the WooCommerce settings. Use the Advanced tab for spacing and visibility.', 'fw' );
	}
}
