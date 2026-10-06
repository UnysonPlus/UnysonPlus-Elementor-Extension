<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Feature list — items with sub-text, value, icon, per-item marker colour, check state and
 * link; check / numbered / bullet markers; vertical or horizontal, columns, dividers.
 */
class FW_Elementor_Widget_Feature_List extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'feature_list';
	}

	public function get_title() {
		return __( 'Feature List', 'fw' );
	}

	public function get_icon() {
		return 'eicon-bullet-list';
	}

	public function get_keywords() {
		return array( 'list', 'features', 'checklist', 'bullets', 'icon list', 'benefits' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'items', 'label' => __( 'Items', 'fw' ), 'tab' => 'content', 'options' => array( 'items' ) ),
			array( 'id' => 'layout', 'label' => __( 'Marker & Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'orientation', 'icon_position', 'icon_style', 'columns', 'dividers', 'zebra', 'spacing_size' ) ),
			array( 'id' => 'presets', 'label' => __( 'Box & Marker', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'icon_badge_preset', 'marker_color', 'marker_size' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'sub_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'items' => array(
				'title_field' => '{{{ text || "Item" }}}',
				'default'     => array(
					array( 'text' => __( 'Free setup and onboarding', 'fw' ) ),
					array( 'text' => __( 'Cancel any time', 'fw' ) ),
					array( 'text' => __( 'Support from real people', 'fw' ) ),
				),
			),
		);
	}
}
