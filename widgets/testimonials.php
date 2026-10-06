<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Testimonials — every design (carousel, grid, marquee, masonry, split, spotlight…),
 * the Card Rows slot designer, the rating style and the colour presets.
 *
 * Not exposed: a testimonial's "Extra Texts" rows (a list inside a list item — Elementor
 * repeaters do not nest) and the card wireframe preview (the canvas is the preview).
 */
class FW_Elementor_Widget_Testimonials extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'testimonials';
	}

	public function get_title() {
		return __( 'Testimonials', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-testimonial-carousel';
	}

	public function get_keywords() {
		return array( 'testimonial', 'review', 'quote', 'carousel', 'slider', 'rating' );
	}

	protected function sections() {
		return array(
			array(
				'id'      => 'items',
				'label'   => __( 'Testimonials', 'fw' ),
				'tab'     => 'content',
				'options' => array( 'testimonials' ),
			),
			array(
				'id'      => 'design',
				'label'   => __( 'Design', 'fw' ),
				'tab'     => 'content',
				'options' => array( 'design_settings' ),
			),
			array(
				'id'      => 'settings',
				'label'   => __( 'Settings', 'fw' ),
				'tab'     => 'content',
				'options' => array( 'container_type', 'reviews_schema' ),
			),
			array(
				'id'      => 'card',
				'label'   => __( 'Card', 'fw' ),
				'tab'     => 'style',
				'options' => array( 'box_style', 'card_rows', 'text_align', 'avatar_shape', 'avatar_size' ),
			),
			array(
				'id'      => 'rating',
				'label'   => __( 'Rating', 'fw' ),
				'tab'     => 'style',
				'options' => array( 'rating_symbol', 'rating_size', 'rating_fill_color', 'rating_empty_color' ),
			),
			array(
				'id'      => 'colors',
				'label'   => __( 'Text & Colors', 'fw' ),
				'tab'     => 'style',
				'options' => array( 'font_size_preset', 'text_color', 'bg_color', 'quote_color', 'author_name_color', 'author_job_color', 'site_link_color' ),
			),
		);
	}

	protected function control_overrides() {
		return array(
			// A new widget starts with something to look at, not "No testimonials found."
			'testimonials' => array(
				'title_field' => '{{{ author_name || "Testimonial" }}}',
				'default'     => array(
					array(
						'content'     => __( 'Working with this team changed how we ship. Clear, fast, and genuinely easy to work with.', 'fw' ),
						'author_name' => __( 'Alex Rivera', 'fw' ),
						'author_job'  => __( 'Product Lead', 'fw' ),
						'rating'      => 5,
					),
					array(
						'content'     => __( 'Reliable from day one. Every question answered, every deadline met.', 'fw' ),
						'author_name' => __( 'Sam Chen', 'fw' ),
						'author_job'  => __( 'Founder', 'fw' ),
						'rating'      => 5,
					),
					array(
						'content'     => __( 'The best decision we made this year. I recommend them to everyone.', 'fw' ),
						'author_name' => __( 'Jordan Lee', 'fw' ),
						'author_job'  => __( 'Operations Director', 'fw' ),
						'rating'      => 4.5,
					),
				),
			),
			'card_rows' => array(
				'title_field' => '{{{ ( slots || [] ).join( " + " ) || "Row" }}}',
			),
		);
	}
}
