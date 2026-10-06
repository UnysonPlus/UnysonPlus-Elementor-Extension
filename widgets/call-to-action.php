<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Call to action — title, message and button, box preset, colours. Not exposed: the column split (kept in the residue). */
class FW_Elementor_Widget_Call_To_Action extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'call_to_action';
	}

	public function get_title() {
		return __( 'Call to Action', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-image-rollover';
	}

	public function get_keywords() {
		return array( 'cta', 'call to action', 'banner', 'button' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'content', 'label' => __( 'Content', 'fw' ), 'tab' => 'content', 'options' => array( 'title', 'message', 'button_label', 'button_link', 'button_target' ) ),
			array( 'id' => 'colors', 'label' => __( 'Box & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'font_size_preset', 'bg_color', 'title_color', 'message_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'title'        => array( 'default' => __( 'Ready to get started?', 'fw' ) ),
			'message'      => array( 'default' => __( 'Tell visitors what happens next and why it is worth it.', 'fw' ) ),
			'button_label' => array( 'default' => __( 'Get in touch', 'fw' ) ),
		);
	}
}
