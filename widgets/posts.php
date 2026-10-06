<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Posts / loop grid — query, every layout and card design, card rows, meta, excerpt, read-more, pagination, live filters. */
class FW_Elementor_Widget_Posts extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'posts';
	}

	public function get_title() {
		return __( 'Posts', 'fw' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_keywords() {
		return array( 'posts', 'blog', 'loop', 'grid', 'articles', 'news' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'query', 'label' => __( 'Query', 'fw' ), 'tab' => 'content', 'options' => array( 'use_current_query', 'post_type', 'taxonomy_filter', 'taxonomy_relation', 'include_ids', 'exclude_ids', 'author_ids', 'date_range', 'posts_per_page', 'offset', 'orderby', 'meta_key', 'order', 'exclude_current', 'sticky_handling' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'card', 'image_size', 'image_ratio', 'fallback_image_url', 'card_padding', 'text_align', 'mobile_layout_override' ) ),
			array( 'id' => 'card', 'label' => __( 'Card Content', 'fw' ), 'tab' => 'content', 'options' => array( 'card_rows', 'title_tag', 'cat_position', 'cat_taxonomy', 'cat_max', 'meta_items', 'meta_layout', 'date_format', 'excerpt_source', 'excerpt_length', 'excerpt_suffix', 'readmore', 'readmore_text' ) ),
			array( 'id' => 'paging', 'label' => __( 'Pagination & Filters', 'fw' ), 'tab' => 'content', 'options' => array( 'pagination', 'live_filters', 'filters_position', 'no_results_text', 'cache_output', 'cache_hours' ) ),
			array( 'id' => 'presets', 'label' => __( 'Presets', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'image_style' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'bg_color', 'title_color', 'excerpt_color', 'meta_color', 'chip_bg', 'chip_color', 'accent_color' ) ),
		);
	}
}
