<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Timeline — milestones with date, text, icon, image and link; four layouts; HowTo schema. */
class FW_Elementor_Widget_Timeline extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'timeline';
	}

	public function get_title() {
		return __( 'Timeline', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-time-line';
	}

	public function get_keywords() {
		return array( 'timeline', 'history', 'milestones', 'roadmap', 'journey' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'items', 'label' => __( 'Milestones', 'fw' ), 'tab' => 'content', 'options' => array( 'items' ) ),
			array( 'id' => 'design', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'marker', 'card_style', 'howto_schema' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'icon_badge_preset', 'line_color', 'card_bg', 'date_color', 'title_color', 'text_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'items' => array(
				'title_field' => '{{{ date }}} — {{{ title }}}',
				'default'     => array(
					array( 'date' => '2019', 'title' => __( 'Founded', 'fw' ), 'text' => __( 'Started with two people and one idea.', 'fw' ) ),
					array( 'date' => '2021', 'title' => __( 'First 100 customers', 'fw' ), 'text' => __( 'Grew by word of mouth alone.', 'fw' ) ),
					array( 'date' => '2024', 'title' => __( 'New office', 'fw' ), 'text' => __( 'A home for a team of twenty.', 'fw' ) ),
				),
			),
		);
	}
}
