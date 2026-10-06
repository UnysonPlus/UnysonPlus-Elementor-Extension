<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Icon box / feature card — icon, title and text, six icon positions, box link, presets. */
class FW_Elementor_Widget_Icon_Box extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'icon_box';
	}

	public function get_title() {
		return __( 'Icon Box', 'fw' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	public function get_keywords() {
		return array( 'icon', 'box', 'feature', 'card', 'service' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'content', 'label' => __( 'Content', 'fw' ), 'tab' => 'content', 'options' => array( 'icon', 'title', 'title_tag', 'content' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'style', 'icon_align', 'title_align', 'content_align', 'mobile_stack', 'full_height' ) ),
			array( 'id' => 'link', 'label' => __( 'Link', 'fw' ), 'tab' => 'content', 'options' => array( 'box_link', 'link_target', 'link_rel' ) ),
			array( 'id' => 'presets', 'label' => __( 'Box & Icon', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'icon_badge_preset', 'icon_size', 'icon_color' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'bg_color', 'title_color', 'content_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'icon'    => array( 'default' => array( 'value' => 'fas fa-star', 'library' => 'fa-solid' ) ),
			'title'   => array( 'default' => __( 'A clear benefit', 'fw' ) ),
			'content' => array( 'default' => __( 'One or two sentences that explain why it matters to the reader.', 'fw' ) ),
		);
	}
}
