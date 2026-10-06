<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Steps / process — every design, marker and connector, Card Rows and the colour presets. */
class FW_Elementor_Widget_Steps extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'steps';
	}

	public function get_title() {
		return __( 'Steps', 'fw' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_keywords() {
		return array( 'steps', 'process', 'how it works', 'timeline', 'numbered' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'items', 'label' => __( 'Steps', 'fw' ), 'tab' => 'content', 'options' => array( 'steps' ) ),
			array( 'id' => 'design', 'label' => __( 'Design', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'marker', 'marker_shape', 'connector', 'title_tag' ) ),
			array( 'id' => 'card', 'label' => __( 'Card', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'card_rows' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'icon_badge_preset', 'marker_text_color', 'title_color', 'text_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'steps'     => array(
				'title_field' => '{{{ title || "Step" }}}',
				'default'     => array(
					array( 'title' => __( 'Tell us what you need', 'fw' ), 'content' => __( 'A short call to understand your goals and constraints.', 'fw' ) ),
					array( 'title' => __( 'Get a clear plan', 'fw' ), 'content' => __( 'A written proposal with scope, timeline and a fixed price.', 'fw' ) ),
					array( 'title' => __( 'We deliver', 'fw' ), 'content' => __( 'Regular check-ins until it ships, then support after launch.', 'fw' ) ),
				),
			),
			'card_rows' => array( 'title_field' => '{{{ ( slots || [] ).join( " + " ) || "Row" }}}' ),
		);
	}
}
