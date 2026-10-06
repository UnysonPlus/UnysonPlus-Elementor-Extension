<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Countdown — target date, units and labels, what happens at zero, box preset, colours. Not exposed: the number / label typography groups. */
class FW_Elementor_Widget_Countdown extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'countdown';
	}

	public function get_title() {
		return __( 'Countdown', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-countdown';
	}

	public function get_keywords() {
		return array( 'countdown', 'timer', 'launch', 'sale', 'deadline' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'target', 'label' => __( 'Target', 'fw' ), 'tab' => 'content', 'options' => array( 'target', 'on_complete', 'complete_text' ) ),
			array( 'id' => 'units', 'label' => __( 'Units & Labels', 'fw' ), 'tab' => 'content', 'options' => array( 'show_days', 'label_days', 'show_hours', 'label_hours', 'show_minutes', 'label_minutes', 'show_seconds', 'label_seconds' ) ),
			array( 'id' => 'style', 'label' => __( 'Box & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'alignment', 'box_preset', 'number_color', 'label_color' ) ),
		);
	}
}
