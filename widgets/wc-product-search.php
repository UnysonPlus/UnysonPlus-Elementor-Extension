<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Product search — a search box scoped to products: placeholder, button text / icon, layout, shape, size, button preset. */
class FW_Elementor_Widget_Wc_Product_Search extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_product_search';
	}

	public function get_title() {
		return __( 'Product Search', 'fw' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'search', 'products', 'shop', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'search', 'label' => __( 'Search', 'fw' ), 'tab' => 'content', 'options' => array( 'placeholder', 'button_text', 'button_icon' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'layout', 'field_shape', 'size', 'button_style', 'width', 'alignment' ) ),
		);
	}
}
