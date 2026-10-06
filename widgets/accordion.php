<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Accordion / FAQ — items, icons, numbering, behaviour, FAQ schema, every style. */
class FW_Elementor_Widget_Accordion extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'accordion';
	}

	public function get_title() {
		return __( 'Accordion', 'fw' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_keywords() {
		return array( 'accordion', 'faq', 'toggle', 'collapse', 'questions' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'items', 'label' => __( 'Items', 'fw' ), 'tab' => 'content', 'options' => array( 'tabs' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'title_tag', 'icon_style', 'icon_position', 'icon_closed_image', 'icon_open_image', 'icon_closed_text', 'icon_open_text', 'numbering', 'numbering_start', 'item_spacing', 'title_alignment' ) ),
			array( 'id' => 'behaviour', 'label' => __( 'Behaviour', 'fw' ), 'tab' => 'content', 'options' => array( 'initially_open', 'collapsible', 'multiple_open', 'hash_linking', 'show_expand_collapse_all', 'faq_schema' ) ),
			array( 'id' => 'design', 'label' => __( 'Design', 'fw' ), 'tab' => 'style', 'options' => array( 'accordion_style', 'corner_radius', 'elevation', 'active_accent', 'title_hover' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'tab_title_color', 'title_bg_color', 'tab_content_color', 'content_bg_color', 'icon_closed_color', 'icon_open_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'tabs' => array(
				'title_field' => '{{{ tab_title || "Item" }}}',
				'default'     => array(
					array( 'tab_title' => __( 'How long does a project take?', 'fw' ), 'tab_content' => __( 'Most projects take four to eight weeks from the first call to launch.', 'fw' ) ),
					array( 'tab_title' => __( 'Can I make changes later?', 'fw' ), 'tab_content' => __( 'Yes. Everything is editable after launch, and we can help when you need it.', 'fw' ) ),
					array( 'tab_title' => __( 'What does it cost?', 'fw' ), 'tab_content' => __( 'You get a fixed price up front, based on the scope we agree together.', 'fw' ) ),
				),
			),
		);
	}
}
