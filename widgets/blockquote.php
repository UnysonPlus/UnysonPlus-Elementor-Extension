<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Blockquote — quote, author, role, source link, quote mark, designs, box preset, colours. */
class FW_Elementor_Widget_Blockquote extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'blockquote';
	}

	public function get_title() {
		return __( 'Blockquote', 'fw' );
	}

	public function get_icon() {
		return 'eicon-blockquote';
	}

	public function get_keywords() {
		return array( 'quote', 'blockquote', 'pull quote', 'citation' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'quote', 'label' => __( 'Quote', 'fw' ), 'tab' => 'content', 'options' => array( 'quote', 'author', 'role', 'source_url', 'show_mark' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'align', 'max_width', 'box_style' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'quote_color', 'accent_color', 'author_color', 'bg_color' ) ),
		);
	}
}
