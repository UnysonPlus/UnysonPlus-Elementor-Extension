<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Table — rows of cells (one per line), row types, table / frame presets, display options
 * and the visitor sort / search / pagination enhancer.
 *
 * Pricing-mode cells (amount, button, switch) and merged cells have no panel control;
 * they ride in the row's residue and render exactly. See FW_Elementor_Option_Bridge's
 * table codec.
 */
class FW_Elementor_Widget_Table extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'table';
	}

	public function get_title() {
		return __( 'Table', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-table';
	}

	public function get_keywords() {
		return array( 'table', 'data', 'spreadsheet', 'comparison', 'rows', 'columns' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'table', 'label' => __( 'Table', 'fw' ), 'tab' => 'content', 'options' => array( 'table', 'caption', 'caption_position' ) ),
			array( 'id' => 'visitors', 'label' => __( 'Sort, Search & Pages', 'fw' ), 'tab' => 'content', 'options' => array( 'enable_sort', 'enable_search', 'enable_pagination', 'pagination_length', 'enable_length_change', 'enable_info' ) ),
			array( 'id' => 'presets', 'label' => __( 'Presets & Display', 'fw' ), 'tab' => 'style', 'options' => array( 'table_preset', 'frame_preset', 'style_striped', 'style_hover', 'style_bordered', 'style_condensed', 'sticky_header' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'bg_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'table' => array(
				'default' => array(
					array( 'row' => 'heading-row', 'cells' => "Plan\nStorage\nSupport" ),
					array( 'row' => 'default-row', 'cells' => "Starter\n5 GB\nEmail" ),
					array( 'row' => 'default-row', 'cells' => "Pro\n50 GB\nPriority" ),
				),
			),
		);
	}
}
