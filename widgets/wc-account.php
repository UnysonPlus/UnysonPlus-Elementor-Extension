<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Account link — the visitor's account menu (log in / my account), hover or click. */
class FW_Elementor_Widget_Wc_Account extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'wc_account';
	}

	public function get_title() {
		return __( 'Account Link', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return array( 'unysonplus-shop' );
	}

	public function get_keywords() {
		return array( 'account', 'login', 'my account', 'woocommerce' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'link', 'label' => __( 'Link', 'fw' ), 'tab' => 'content', 'options' => array( 'show_label', 'trigger' ) ),
		);
	}
}
