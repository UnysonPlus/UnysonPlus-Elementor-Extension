<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Rive animation — a .riv file with state machine, triggers, scroll input, data bindings and asset swaps. */
class FW_Elementor_Widget_Rive extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'rive';
	}

	public function get_title() {
		return __( 'Rive', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-animation';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'rive', 'animation', 'interactive', 'state machine' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'animation', 'label' => __( 'Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'src', 'src_url', 'artboard', 'state_machine', 'anim_name', 'poster' ) ),
			array( 'id' => 'interaction', 'label' => __( 'Interaction', 'fw' ), 'tab' => 'content', 'options' => array( 'trigger', 'click_trigger', 'scroll_input', 'scroll_length', 'input_feeds', 'event_open_url', 'event_url_target', 'event_js_hook' ) ),
			array( 'id' => 'data', 'label' => __( 'Data', 'fw' ), 'tab' => 'content', 'options' => array( 'bindings', 'asset_swaps' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'fit', 'layout_scale', 'alignment', 'height', 'snug', 'background' ) ),
		);
	}
}
