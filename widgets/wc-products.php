<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Products — any WooCommerce query (category, tags, attributes, ids), grid / list / carousel, pagination, Card Rows, rating, badges, add-to-cart. */
class FW_Elementor_Widget_Wc_Products extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_products';
	}

	public function get_title() {
		return __( 'Products', 'fw' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'products', 'shop', 'woocommerce', 'grid', 'catalog' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'query', 'label' => __( 'Query', 'fw' ), 'tab' => 'content', 'options' => array( 'source', 'category', 'tags', 'attribute', 'attribute_terms', 'product_ids', 'posts_per_page', 'orderby', 'order' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'layout', 'columns', 'gap', 'alignment', 'pagination', 'carousel_arrows' ) ),
			array( 'id' => 'card', 'label' => __( 'Card', 'fw' ), 'tab' => 'style', 'options' => array( 'card_rows', 'box_style', 'image_ratio', 'image_size' ) ),
			array( 'id' => 'badges', 'label' => __( 'Rating & Badges', 'fw' ), 'tab' => 'style', 'options' => array( 'rating_symbol', 'rating_size', 'rating_fill_color', 'rating_empty_color', 'show_ribbon', 'show_sale_badge', 'badge_style', 'show_featured_badge', 'show_new_badge', 'new_days' ) ),
			array( 'id' => 'cart', 'label' => __( 'Add to Cart', 'fw' ), 'tab' => 'style', 'options' => array( 'add_to_cart_text', 'cart_style', 'cart_btn_style', 'cart_btn_size', 'quick_view_placement' ) ),
		);
	}
}
