<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Text scroll choreography — text layers that move, curve and scrub as the page scrolls. */
class FW_Elementor_Widget_Text_Scroll_Choreography extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'text_scroll_choreography';
	}

	public function get_title() {
		return __( 'Text Scroll Choreography', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-animation-text';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'scroll', 'choreography', 'text', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'layers', 'label' => __( 'Text Layers', 'fw' ), 'tab' => 'content', 'options' => array( 'recipe', 'layers' ) ),
			array( 'id' => 'motion', 'label' => __( 'Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'layer_z', 'travel', 'smooth', 'guide', 'custom_keys' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'layers' => array(
				'title_field' => '{{{ text || "Layer" }}}',
				'default'     => array(
					array( 'text' => __( 'Built to move', 'fw' ), 'anchor' => 'center-left' ),
					array( 'text' => __( 'with your scroll', 'fw' ), 'anchor' => 'center-right', 'talign' => 'right', 'appear_from' => 'above', 'z' => 2 ),
				),
			),
		);
	}
}
