<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** WebGL object — a live 3D shader object (blob, sphere, waves…) that follows the pointer and scroll. */
class FW_Elementor_Widget_Webgl_Object extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'webgl_object';
	}

	public function get_title() {
		return __( 'WebGL Object', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-globe';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'webgl', '3d', 'blob', 'shader', 'hero', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'object', 'label' => __( 'Object', 'fw' ), 'tab' => 'content', 'options' => array( 'style_preset', 'scale', 'placement', 'poster' ) ),
			array( 'id' => 'motion', 'label' => __( 'Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'auto_rotate', 'noise_amount', 'noise_speed', 'scroll_link', 'pointer_follow', 'pointer_strength', 'parallax' ) ),
			array( 'id' => 'performance', 'label' => __( 'Performance', 'fw' ), 'tab' => 'content', 'options' => array( 'quality', 'dpr_cap' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'color_a', 'color_b', 'background', 'bg_color' ) ),
		);
	}
}
