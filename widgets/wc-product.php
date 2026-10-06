<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Product card — one chosen product as a card: Card Rows, rating, badges, add-to-cart, quick view. */
class FW_Elementor_Widget_Wc_Product extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_product';
	}

	public function get_title() {
		return __( 'Product Card', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-single-product';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'product', 'card', 'woocommerce', 'featured' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'product', 'label' => __( 'Product', 'fw' ), 'tab' => 'content', 'options' => array( 'product' ) ),
			array( 'id' => 'card', 'label' => __( 'Card', 'fw' ), 'tab' => 'style', 'options' => array( 'card_rows', 'box_style', 'image_ratio', 'image_size' ) ),
			array( 'id' => 'badges', 'label' => __( 'Rating & Badges', 'fw' ), 'tab' => 'style', 'options' => array( 'rating_symbol', 'rating_size', 'rating_fill_color', 'rating_empty_color', 'show_ribbon', 'show_sale_badge', 'badge_style', 'show_featured_badge', 'show_new_badge', 'new_days' ) ),
			array( 'id' => 'cart', 'label' => __( 'Add to Cart', 'fw' ), 'tab' => 'style', 'options' => array( 'add_to_cart_text', 'cart_style', 'cart_btn_style', 'cart_btn_size', 'quick_view_placement' ) ),
		);
	}

	/** Starts on the first product, so a new widget shows a real card. */
	protected function control_overrides() {
		$first = $this->first_choice( 'product' );
		return '' === $first ? array() : array( 'product' => array( 'default' => $first ) );
	}
}
