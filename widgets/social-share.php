<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Share buttons — networks, what is shared, every design, shape, size, labels, colours. */
class FW_Elementor_Widget_Social_Share extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'social_share';
	}

	public function get_title() {
		return __( 'Share Buttons', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-share';
	}

	public function get_keywords() {
		return array( 'share', 'social', 'buttons', 'facebook', 'linkedin' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'networks', 'label' => __( 'Networks', 'fw' ), 'tab' => 'content', 'options' => array( 'networks', 'share_source', 'custom_url', 'share_text' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'shape', 'size', 'show_label', 'layout', 'align', 'font_size_preset', 'custom_color', 'icon_color' ) ),
		);
	}
}
