<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Slides / carousel — slides with image, heading, text and button; per-view per device, effect, navigation, autoplay, overlay. */
class FW_Elementor_Widget_Carousel extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'carousel';
	}

	public function get_title() {
		return __( 'Slides', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-slides';
	}

	public function get_keywords() {
		return array( 'slides', 'slider', 'carousel', 'hero slider' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'slides', 'label' => __( 'Slides', 'fw' ), 'tab' => 'content', 'options' => array( 'slides' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'per_page', 'per_page_tablet', 'per_page_mobile', 'gap', 'height', 'effect' ) ),
			array( 'id' => 'motion', 'label' => __( 'Navigation & Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'arrows', 'pagination', 'autoplay', 'interval', 'speed', 'pause_hover', 'loop', 'drag' ) ),
			array( 'id' => 'style', 'label' => __( 'Overlay & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'overlay', 'overlay_opacity', 'heading_color', 'text_color' ) ),
		);
	}

	protected function control_overrides() {
		$slide = function ( $heading, $text ) {
			return array( 'image' => $this->placeholder_image(), 'heading' => $heading, 'text' => $text, 'button_label' => __( 'Learn more', 'fw' ), 'button_link' => '#' );
		};

		return array(
			'slides' => array(
				'title_field' => '{{{ heading || "Slide" }}}',
				'default'     => array(
					$slide( __( 'Built for the way you work', 'fw' ), __( 'A short line that says why it matters.', 'fw' ) ),
					$slide( __( 'Ready in minutes', 'fw' ), __( 'No setup, no learning curve.', 'fw' ) ),
					$slide( __( 'Here when you need us', 'fw' ), __( 'Real people, fast answers.', 'fw' ) ),
				),
			),
		);
	}
}
