<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** SVG morph — a shape that morphs through a list of shapes. */
class FW_Elementor_Widget_Svg_Morph extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'svg_morph';
	}

	public function get_title() {
		return __( 'SVG Morph', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-shape';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'svg', 'morph', 'shape', 'blob', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'shapes', 'label' => __( 'Shapes', 'fw' ), 'tab' => 'content', 'options' => array( 'shapes_list', 'loopback', 'render_mode' ) ),
			array( 'id' => 'animation', 'label' => __( 'Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'trigger', 'easing' ) ),
			array( 'id' => 'style', 'label' => __( 'Fill & Stroke', 'fw' ), 'tab' => 'style', 'options' => array( 'fill_color', 'stroke_color', 'stroke_width', 'max_width', 'align' ) ),
		);
	}
}
