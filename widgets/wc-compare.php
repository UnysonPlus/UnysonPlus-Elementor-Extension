<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Compare — the visitor's products side by side, with an empty state. */
class FW_Elementor_Widget_Wc_Compare extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_compare';
	}

	public function get_title() {
		return __( 'Compare', 'fw' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'compare', 'comparison', 'products', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'compare', 'label' => __( 'Compare', 'fw' ), 'tab' => 'content', 'options' => array( 'empty_text', 'empty_label', 'empty_link' ) ),
		);
	}
}
