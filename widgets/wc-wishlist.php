<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Wishlist — the visitor's saved products, with an empty state. */
class FW_Elementor_Widget_Wc_Wishlist extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_wishlist';
	}

	public function get_title() {
		return __( 'Wishlist', 'fw' );
	}

	public function get_icon() {
		return 'eicon-heart';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'wishlist', 'favourites', 'saved', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'wishlist', 'label' => __( 'Wishlist', 'fw' ), 'tab' => 'content', 'options' => array( 'columns', 'empty_text', 'empty_label', 'empty_link' ) ),
		);
	}
}
