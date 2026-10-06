<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Table of contents — heading levels, hierarchy, numbering, scope, collapse, smooth scroll, scroll-spy, sticky, colours. */
class FW_Elementor_Widget_Toc extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'toc';
	}

	public function get_title() {
		return __( 'Table of Contents', 'fw' );
	}

	public function get_icon() {
		return 'eicon-table-of-contents';
	}

	public function get_keywords() {
		return array( 'toc', 'table of contents', 'contents', 'anchors', 'headings' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'content', 'label' => __( 'Headings', 'fw' ), 'tab' => 'content', 'options' => array( 'title', 'levels', 'hierarchical', 'min_headings', 'numeration', 'numeration_suffix', 'scope', 'scope_selector', 'skip_text' ) ),
			array( 'id' => 'behaviour', 'label' => __( 'Behaviour', 'fw' ), 'tab' => 'content', 'options' => array( 'collapsible', 'collapsed_default', 'label_show', 'label_hide', 'smooth_scroll', 'scroll_offset', 'scrollspy', 'nofollow', 'noindex' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'width', 'custom_width', 'float', 'sticky', 'sticky_offset', 'title_size', 'items_size' ) ),
			array( 'id' => 'colors', 'label' => __( 'Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'bg_color', 'border_color', 'title_color', 'link_color', 'link_hover_color', 'link_active_color' ) ),
		);
	}
}
