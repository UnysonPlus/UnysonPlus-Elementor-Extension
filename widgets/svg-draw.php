<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** SVG draw — an SVG whose strokes draw themselves on view, on load, on hover or with scroll. */
class FW_Elementor_Widget_Svg_Draw extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'svg_draw';
	}

	public function get_title() {
		return __( 'SVG Draw', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-pencil';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'svg', 'draw', 'line', 'stroke', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'svg', 'label' => __( 'SVG', 'fw' ), 'tab' => 'content', 'options' => array( 'svg' ) ),
			array( 'id' => 'animation', 'label' => __( 'Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'trigger', 'duration', 'stagger', 'direction', 'loop' ) ),
			array( 'id' => 'style', 'label' => __( 'Stroke & Fill', 'fw' ), 'tab' => 'style', 'options' => array( 'stroke_width', 'stroke_color', 'fill_after', 'fill_color', 'max_width', 'align' ) ),
		);
	}
}
