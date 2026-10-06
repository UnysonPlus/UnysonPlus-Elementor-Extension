<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Image sequence — a frame sequence played by scroll (pinned) or autoplay. */
class FW_Elementor_Widget_Image_Sequence extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'image_sequence';
	}

	public function get_title() {
		return __( 'Image Sequence', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-video-playlist';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'image sequence', 'scroll', 'frames', 'flipbook', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'frames', 'label' => __( 'Frames', 'fw' ), 'tab' => 'content', 'options' => array( 'frames_source' ) ),
			array( 'id' => 'playback', 'label' => __( 'Playback', 'fw' ), 'tab' => 'content', 'options' => array( 'mode', 'pin_length', 'direction' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'fit', 'height', 'bg' ) ),
		);
	}
}
