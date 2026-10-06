<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Image box — image, subtitle, title, text, icon and button; every design, hover effect, link behaviour, presets, colours. */
class FW_Elementor_Widget_Image_Box extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'image_box';
	}

	public function get_title() {
		return __( 'Image Box', 'fw' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	public function get_keywords() {
		return array( 'image', 'box', 'card', 'feature', 'service' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'content', 'label' => __( 'Content', 'fw' ), 'tab' => 'content', 'options' => array( 'image', 'image_alt', 'subtitle', 'title', 'title_tag', 'text', 'icon', 'button_style', 'button_label' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design_settings', 'image_ratio', 'content_align', 'image_size', 'hover_effect', 'transition_speed' ) ),
			array( 'id' => 'link', 'label' => __( 'Link', 'fw' ), 'tab' => 'content', 'options' => array( 'link_behavior', 'link_url', 'link_target' ) ),
			array( 'id' => 'presets', 'label' => __( 'Presets', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'image_style', 'icon_badge_preset', 'button_preset', 'button_size' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'bg_color', 'accent_color', 'title_color', 'subtitle_color', 'content_color', 'icon_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'image' => array( 'default' => $this->placeholder_image() ),
			'title' => array( 'default' => __( 'A clear benefit', 'fw' ) ),
			'text'  => array( 'default' => __( 'One or two sentences that explain why it matters to the reader.', 'fw' ) ),
		);
	}
}
