<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Menu cart — a cart icon with count opening a dropdown or drawer: trigger, backdrop, labels, empty state. */
class FW_Elementor_Widget_Wc_Mini_Cart extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_mini_cart';
	}

	public function get_title() {
		return __( 'Menu Cart', 'fw' );
	}

	public function get_icon() {
		return 'eicon-cart';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'cart', 'mini cart', 'menu cart', 'basket', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'toggle', 'label' => __( 'Icon & Trigger', 'fw' ), 'tab' => 'content', 'options' => array( 'icon', 'trigger', 'show_count' ) ),
			array( 'id' => 'panel', 'label' => __( 'Panel', 'fw' ), 'tab' => 'content', 'options' => array( 'panel_style', 'drawer_backdrop', 'drawer_backdrop_blur', 'panel_title', 'subtotal_label', 'checkout_text', 'footnote' ) ),
			array( 'id' => 'empty', 'label' => __( 'Empty Cart', 'fw' ), 'tab' => 'content', 'options' => array( 'empty_icon', 'empty_heading', 'empty_text', 'empty_button_label', 'empty_button_url' ) ),
		);
	}
}
