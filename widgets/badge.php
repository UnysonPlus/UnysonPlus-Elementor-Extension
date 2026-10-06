<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Badge / announcement pill — sub-tag + message + optional link, icons, styles, dismiss, schema. */
class FW_Elementor_Widget_Badge extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'badge';
	}

	public function get_title() {
		return __( 'Badge', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-alert';
	}

	public function get_keywords() {
		return array( 'badge', 'pill', 'announcement', 'chip', 'eyebrow', 'new' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'text', 'label' => __( 'Text & Link', 'fw' ), 'tab' => 'content', 'options' => array( 'tag_text', 'message', 'link', 'link_target', 'rel_nofollow', 'rel_sponsored', 'rel_ugc' ) ),
			array( 'id' => 'icons', 'label' => __( 'Icons', 'fw' ), 'tab' => 'content', 'options' => array( 'leading', 'leading_icon', 'trailing_icon' ) ),
			array( 'id' => 'extras', 'label' => __( 'Dismiss, Accessibility & Schema', 'fw' ), 'tab' => 'content', 'options' => array( 'dismissible', 'dismiss_id', 'aria_label', 'title_attr', 'schema_enable', 'schema_name', 'schema_date' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'style', 'shape', 'size', 'align', 'tag_style', 'hover' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'pill_color', 'text_color', 'tag_color', 'gradient_from', 'gradient_to' ) ),
		);
	}
}
