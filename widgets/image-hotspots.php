<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Image hotspots — an image with positioned pins and pop-overs, designs, trigger, colours. */
class FW_Elementor_Widget_Image_Hotspots extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'image_hotspots';
	}

	public function get_title() {
		return __( 'Hotspot', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-image-hotspot';
	}

	public function get_keywords() {
		return array( 'hotspot', 'image', 'pins', 'tooltip', 'map' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'content', 'label' => __( 'Image & Hotspots', 'fw' ), 'tab' => 'content', 'options' => array( 'image', 'hotspots' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'trigger', 'pin_size', 'rounded' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'pin_color', 'pop_bg', 'pop_color', 'accent_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'image'    => array( 'default' => $this->placeholder_image() ),
			'hotspots' => array(
				'title_field' => '{{{ title || "Hotspot" }}}',
				'default'     => array(
					array( 'x' => 30, 'y' => 40, 'title' => __( 'First detail', 'fw' ), 'text' => __( 'What the visitor should notice here.', 'fw' ) ),
					array( 'x' => 70, 'y' => 60, 'title' => __( 'Second detail', 'fw' ), 'text' => __( 'Another point worth a closer look.', 'fw' ) ),
				),
			),
		);
	}
}
