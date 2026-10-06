<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Flip box — front and back faces (icon, title, text, image), the back button, flip / reveal effects, trigger, presets. */
class FW_Elementor_Widget_Flip_Box extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'flip_box';
	}

	public function get_title() {
		return __( 'Flip Box', 'fw' );
	}

	public function get_icon() {
		return 'eicon-flip-box';
	}

	public function get_keywords() {
		return array( 'flip', 'box', 'card', 'hover', 'reveal' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'front', 'label' => __( 'Front', 'fw' ), 'tab' => 'content', 'options' => array( 'front_icon', 'front_title', 'front_title_tag', 'front_text', 'front_image' ) ),
			array( 'id' => 'back', 'label' => __( 'Back', 'fw' ), 'tab' => 'content', 'options' => array( 'back_icon', 'back_title', 'back_title_tag', 'back_text', 'back_image', 'button_label', 'button_url', 'button_target' ) ),
			array( 'id' => 'effect', 'label' => __( 'Effect', 'fw' ), 'tab' => 'content', 'options' => array( 'design_settings', 'flip_direction', 'trigger', 'parallax', 'flip_speed', 'flip_easing', 'height' ) ),
			array( 'id' => 'presets', 'label' => __( 'Shape & Presets', 'fw' ), 'tab' => 'style', 'options' => array( 'rounded', 'box_style', 'icon_badge_preset', 'button_style', 'button_size' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'front_bg', 'front_color', 'back_bg', 'back_color' ) ),
		);
	}
}
