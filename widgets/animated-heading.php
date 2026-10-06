<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Animated headline — before / rotating words / after, every animation, highlight, caret, colours. */
class FW_Elementor_Widget_Animated_Heading extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'animated_heading';
	}

	public function get_title() {
		return __( 'Animated Headline', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-animated-headline';
	}

	public function get_keywords() {
		return array( 'animated', 'headline', 'heading', 'typing', 'rotating' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'text', 'label' => __( 'Text', 'fw' ), 'tab' => 'content', 'options' => array( 'before_text', 'words', 'after_text', 'tag' ) ),
			array( 'id' => 'animation', 'label' => __( 'Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'anim', 'speed', 'highlight', 'loop', 'pause_hover', 'randomize', 'caret_show', 'caret_style' ) ),
			array( 'id' => 'style', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'align', 'font_size_preset', 'text_color', 'accent_color', 'caret_color' ) ),
		);
	}
}
