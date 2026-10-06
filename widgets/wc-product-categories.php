<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Product categories — a grid of category cards: which categories, order, columns, Card Rows, button. */
class FW_Elementor_Widget_Wc_Product_Categories extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_product_categories';
	}

	public function get_title() {
		return __( 'Product Categories', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-product-categories';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'categories', 'shop', 'woocommerce', 'collections' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'query', 'label' => __( 'Categories', 'fw' ), 'tab' => 'content', 'options' => array( 'number', 'orderby', 'order', 'parent', 'ids', 'hide_empty' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'columns', 'gap', 'alignment', 'button_text' ) ),
			array( 'id' => 'card', 'label' => __( 'Card', 'fw' ), 'tab' => 'style', 'options' => array( 'card_rows', 'box_style', 'image_ratio', 'image_size' ) ),
		);
	}
}
