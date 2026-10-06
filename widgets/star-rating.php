<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Star rating — value, scale, label, count text, rating schema, designs, colours. */
class FW_Elementor_Widget_Star_Rating extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'star_rating';
	}

	public function get_title() {
		return __( 'Star Rating', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-rating';
	}

	public function get_keywords() {
		return array( 'rating', 'stars', 'review', 'score' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'rating', 'label' => __( 'Rating', 'fw' ), 'tab' => 'content', 'options' => array( 'rating', 'max', 'label', 'show_value', 'count_text', 'rating_schema' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'size', 'align', 'font_size_preset', 'fill_color', 'empty_color', 'text_color' ) ),
		);
	}
}
