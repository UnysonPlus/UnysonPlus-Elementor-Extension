<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Comparison table — columns (products / plans) and feature rows, featured highlight, sticky header, product schema, colours. */
class FW_Elementor_Widget_Comparison_Table extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'comparison_table';
	}

	public function get_title() {
		return __( 'Comparison Table', 'fw' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_keywords() {
		return array( 'comparison', 'compare', 'versus', 'plans', 'features' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'table', 'label' => __( 'Columns & Rows', 'fw' ), 'tab' => 'content', 'options' => array( 'columns', 'rows' ) ),
			array( 'id' => 'options', 'label' => __( 'Options', 'fw' ), 'tab' => 'content', 'options' => array( 'style', 'highlight_featured', 'sticky_header', 'center_cells', 'product_schema' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'header_bg', 'header_text', 'text_color', 'border_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'columns' => array(
				'title_field' => '{{{ name || "Column" }}}',
				'default'     => array(
					array( 'name' => __( 'Basic', 'fw' ), 'price' => '$9' ),
					array( 'name' => __( 'Pro', 'fw' ), 'price' => '$29', 'featured' => 'yes', 'badge' => __( 'Popular', 'fw' ) ),
					array( 'name' => __( 'Team', 'fw' ), 'price' => '$79' ),
				),
			),
			'rows'    => array(
				'title_field' => '{{{ label || "Row" }}}',
				'default'     => array(
					array( 'label' => __( 'Projects', 'fw' ), 'values' => "1\n10\nUnlimited" ),
					array( 'label' => __( 'Priority support', 'fw' ), 'values' => "no\nyes\nyes" ),
					array( 'label' => __( 'Team members', 'fw' ), 'values' => "-\n3\n20" ),
				),
			),
		);
	}
}
