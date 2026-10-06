<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Add to cart button for one product — quantity, label, price, button preset, shape, width. Not exposed: the hover animation picker (kept in the residue). */
class FW_Elementor_Widget_Wc_Add_To_Cart extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_add_to_cart';
	}

	public function get_title() {
		return __( 'Add to Cart', 'fw' );
	}

	public function get_icon() {
		return 'eicon-product-add-to-cart';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'add to cart', 'buy', 'button', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'product', 'label' => __( 'Product', 'fw' ), 'tab' => 'content', 'options' => array( 'product', 'quantity', 'label', 'show_price', 'price_position' ) ),
			array( 'id' => 'button', 'label' => __( 'Button', 'fw' ), 'tab' => 'style', 'options' => array( 'style', 'size', 'shape', 'width', 'alignment' ) ),
		);
	}

	/** Starts on the first product, so a new widget shows a real card. */
	protected function control_overrides() {
		$first = $this->first_choice( 'product' );
		return '' === $first ? array() : array( 'product' => array( 'default' => $first ) );
	}
}
