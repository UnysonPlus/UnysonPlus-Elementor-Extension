<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Image scroll choreography — image layers that move along keyframed paths as the page scrolls. */
class FW_Elementor_Widget_Image_Scroll_Choreography extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'image_scroll_choreography';
	}

	public function get_title() {
		return __( 'Image Scroll Choreography', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-parallax';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'scroll', 'choreography', 'layers', 'images', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'layers', 'label' => __( 'Layers', 'fw' ), 'tab' => 'content', 'options' => array( 'recipe', 'layers' ) ),
			array( 'id' => 'motion', 'label' => __( 'Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'layer_z', 'travel', 'smooth', 'guide', 'custom_keys' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'layers' => array(
				'title_field' => '{{{ anchor }}}',
				'default'     => array(
					array( 'image' => $this->placeholder_image(), 'anchor' => 'bottom-left', 'width' => 30 ),
					array( 'image' => $this->placeholder_image(), 'anchor' => 'top-right', 'width' => 24, 'appear_from' => 'above', 'z' => 2 ),
				),
			),
		);
	}
}
