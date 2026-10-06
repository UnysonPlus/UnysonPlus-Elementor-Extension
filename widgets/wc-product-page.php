<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Product page — a whole single-product layout (gallery, summary, add to cart, tabs) for a chosen product. */
class FW_Elementor_Widget_Wc_Product_Page extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_product_page';
	}

	public function get_title() {
		return __( 'Product Page', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-product-info';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'product', 'single product', 'product page', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'product', 'label' => __( 'Product', 'fw' ), 'tab' => 'content', 'options' => array( 'product' ) ),
		);
	}

	/** Starts on the first product, so a new widget shows a real card. */
	protected function control_overrides() {
		$first = $this->first_choice( 'product' );
		return '' === $first ? array() : array( 'product' => array( 'default' => $first ) );
	}
}
