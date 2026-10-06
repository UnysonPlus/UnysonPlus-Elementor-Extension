<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Progress — bars, circles, gauges, pies, vertical and segmented; animate and count up on scroll. */
class FW_Elementor_Widget_Progress extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'progress';
	}

	public function get_title() {
		return __( 'Progress', 'fw' );
	}

	public function get_icon() {
		return 'eicon-skill-bar';
	}

	public function get_keywords() {
		return array( 'progress', 'skills', 'bar', 'circle', 'gauge', 'percent' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'bars', 'label' => __( 'Bars', 'fw' ), 'tab' => 'content', 'options' => array( 'bars' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'layout' ) ),
			array( 'id' => 'style', 'label' => __( 'Bar Style', 'fw' ), 'tab' => 'style', 'options' => array( 'height', 'gap', 'value_position', 'rounded', 'striped', 'show_value', 'animate', 'count_up' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'fill_color', 'fill_color_2', 'track_color', 'label_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'bars' => array(
				'title_field' => '{{{ label }}} — {{{ percent }}}%',
				'default'     => array(
					array( 'label' => __( 'Design', 'fw' ), 'percent' => 90 ),
					array( 'label' => __( 'Development', 'fw' ), 'percent' => 75 ),
					array( 'label' => __( 'Support', 'fw' ), 'percent' => 95 ),
				),
			),
		);
	}
}
