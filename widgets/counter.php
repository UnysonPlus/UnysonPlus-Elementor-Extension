<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Counter / stat — counts up when scrolled into view.
 *
 * Not exposed: the number / prefix / suffix typography groups (a whole font stack per part
 * has no single Elementor control; Elementor's own Typography is a different value shape).
 * Values carried in from page-builder atts still render through the residue.
 */
class FW_Elementor_Widget_Counter extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'counter';
	}

	public function get_title() {
		return __( 'Counter', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-counter';
	}

	public function get_keywords() {
		return array( 'counter', 'number', 'stat', 'statistics', 'count up' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'value', 'label' => __( 'Value', 'fw' ), 'tab' => 'content', 'options' => array( 'number', 'start', 'prefix', 'suffix' ) ),
			array( 'id' => 'format', 'label' => __( 'Format & Animation', 'fw' ), 'tab' => 'content', 'options' => array( 'decimals', 'separator', 'duration', 'easing' ) ),
			array( 'id' => 'style', 'label' => __( 'Alignment & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'alignment', 'number_color', 'prefix_color', 'suffix_color' ) ),
		);
	}
}
