<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Modal popup — a button / link / icon / image trigger opening a dialog; size, animation, open on load, colours. */
class FW_Elementor_Widget_Modal_Popup extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'modal_popup';
	}

	public function get_title() {
		return __( 'Modal Popup', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-lightbox';
	}

	public function get_keywords() {
		return array( 'modal', 'popup', 'lightbox', 'dialog', 'off-canvas' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'trigger', 'label' => __( 'Trigger', 'fw' ), 'tab' => 'content', 'options' => array( 'trigger_type', 'trigger_label', 'trigger_icon', 'trigger_image' ) ),
			array( 'id' => 'modal', 'label' => __( 'Modal', 'fw' ), 'tab' => 'content', 'options' => array( 'modal_title', 'modal_content', 'design', 'size', 'open_animation', 'open_on_load', 'open_delay', 'close_overlay' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'overlay_color', 'modal_bg', 'modal_color' ) ),
		);
	}
}
