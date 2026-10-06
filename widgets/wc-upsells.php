<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Upsells / related products for the current product or cart. */
class FW_Elementor_Widget_Wc_Upsells extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_upsells';
	}

	public function get_title() {
		return __( 'Upsells', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-product-upsell';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'upsells', 'related', 'cross-sells', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'upsells', 'label' => __( 'Products', 'fw' ), 'tab' => 'content', 'options' => array( 'source', 'heading', 'columns', 'limit' ) ),
		);
	}
}
