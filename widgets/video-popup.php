<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Video popup — a poster with a play button that opens the video in a lightbox; designs, ratio, colours. */
class FW_Elementor_Widget_Video_Popup extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'video_popup';
	}

	public function get_title() {
		return __( 'Video Popup', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-youtube';
	}

	public function get_keywords() {
		return array( 'video', 'popup', 'lightbox', 'youtube', 'vimeo', 'play' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'video', 'label' => __( 'Video', 'fw' ), 'tab' => 'content', 'options' => array( 'poster', 'video_url', 'play_label', 'caption' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'ratio', 'play_size', 'rounded', 'overlay', 'hover_zoom' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'icon_color', 'overlay_color', 'label_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'poster' => array( 'default' => $this->placeholder_image() ),
		);
	}
}
