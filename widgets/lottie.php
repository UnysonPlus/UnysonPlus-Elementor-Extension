<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Lottie animation — URL or uploaded JSON, trigger, loop, reverse on hover, speed, direction. */
class FW_Elementor_Widget_Lottie extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'lottie';
	}

	public function get_title() {
		return __( 'Lottie', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-lottie';
	}

	public function get_keywords() {
		return array( 'lottie', 'animation', 'json', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'animation', 'label' => __( 'Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'source', 'lottie_url', 'lottie_file', 'trigger', 'loop', 'reverse_hover', 'speed', 'direction' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'max_width', 'alignment' ) ),
		);
	}
}
