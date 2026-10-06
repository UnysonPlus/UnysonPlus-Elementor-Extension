<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Product filters — a filter panel (price, categories, attributes, rating…) for the shop, live (AJAX) or on submit. */
class FW_Elementor_Widget_Wc_Product_Filters extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_product_filters';
	}

	public function get_title() {
		return __( 'Product Filters', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'filters', 'facets', 'shop', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'filters', 'label' => __( 'Filters', 'fw' ), 'tab' => 'content', 'options' => array( 'filters', 'panel_title', 'collapsible', 'ajax' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'divider' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'filters' => array(
				'title_field' => '{{{ title || type }}}',
				'default'     => array(
					array( 'type' => 'price', 'title' => __( 'Price', 'fw' ) ),
					array( 'type' => 'rating', 'title' => __( 'Rating', 'fw' ) ),
					array( 'type' => 'active', 'title' => __( 'Active filters', 'fw' ) ),
				),
			),
		);
	}
}
