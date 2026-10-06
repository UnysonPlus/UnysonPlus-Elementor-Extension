<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Interactive reveal — stacked layers revealed by drag, hover, scroll or time, with hotspots. */
class FW_Elementor_Widget_Interactive_Reveal extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'interactive_reveal';
	}

	public function get_title() {
		return __( 'Interactive Reveal', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-image-before-after';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'reveal', 'layers', 'drag', 'scroll', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'layers', 'label' => __( 'Layers', 'fw' ), 'tab' => 'content', 'options' => array( 'layer_source', 'pack', 'layers', 'hotspots' ) ),
			array( 'id' => 'interaction', 'label' => __( 'Interaction', 'fw' ), 'tab' => 'content', 'options' => array( 'trigger', 'drag_axis', 'reverse', 'start_at', 'duration', 'scroll_mode', 'pin_length', 'pointer' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'placement', 'aspect', 'height', 'max_width', 'align', 'bg' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'layers' => array(
				'title_field' => '{{{ label || "Layer" }}}',
				'default'     => array(
					array( 'image' => $this->placeholder_image(), 'label' => __( 'Back', 'fw' ) ),
					array( 'image' => $this->placeholder_image(), 'label' => __( 'Front', 'fw' ) ),
				),
			),
		);
	}
}
