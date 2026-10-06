<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Newsletter signup — heading, fields, consent text, messages and the four designs.
 * Submissions go through the shortcode's own handler (delegated from the document, so a
 * form re-rendered in the editor needs no re-init).
 */
class FW_Elementor_Widget_Newsletter extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'newsletter';
	}

	public function get_title() {
		return __( 'Newsletter', 'fw' );
	}

	public function get_icon() {
		return 'eicon-mail';
	}

	public function get_keywords() {
		return array( 'newsletter', 'subscribe', 'signup', 'email', 'form', 'mailing list' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'text', 'label' => __( 'Text', 'fw' ), 'tab' => 'content', 'options' => array( 'title', 'description' ) ),
			array( 'id' => 'fields', 'label' => __( 'Fields', 'fw' ), 'tab' => 'content', 'options' => array( 'show_name', 'show_field_labels', 'name_label', 'email_label', 'name_placeholder', 'email_placeholder', 'field_icon', 'button_label', 'consent_text' ) ),
			array( 'id' => 'messages', 'label' => __( 'Messages & List', 'fw' ), 'tab' => 'content', 'options' => array( 'success_message', 'error_message', 'list_id' ) ),
			array( 'id' => 'design', 'label' => __( 'Design', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'align', 'rounded' ) ),
			array( 'id' => 'colors', 'label' => __( 'Button & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'button_preset', 'font_size_preset', 'text_color', 'field_bg', 'bg_color', 'field_icon_color' ) ),
		);
	}
}
