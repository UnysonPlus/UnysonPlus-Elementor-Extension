<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Before / after — two images with a draggable divider (or hover / toggle types), ratio, colours. */
class FW_Elementor_Widget_Before_After extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'before_after';
	}

	public function get_title() {
		return __( 'Before / After', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-image-before-after';
	}

	public function get_keywords() {
		return array( 'before', 'after', 'compare', 'slider', 'images' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'images', 'label' => __( 'Images', 'fw' ), 'tab' => 'content', 'options' => array( 'before_image', 'after_image', 'type', 'as_background' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'ratio', 'max_width', 'rounded' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'bg_color', 'divider_color', 'handle_color', 'handle_icon_color', 'label_bg', 'label_text' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'before_image' => array( 'default' => $this->placeholder_image() ),
			'after_image'  => array( 'default' => $this->placeholder_image() ),
		);
	}
}
