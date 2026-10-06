<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Tag list / chips — a row of pill labels, one per line; five looks, shape, size, marker. */
class FW_Elementor_Widget_Tag_List extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'tag_list';
	}

	public function get_title() {
		return __( 'Tag List', 'fw' );
	}

	public function get_icon() {
		return 'eicon-tags';
	}

	public function get_keywords() {
		return array( 'tags', 'chips', 'pills', 'labels', 'keywords' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'items', 'label' => __( 'Tags', 'fw' ), 'tab' => 'content', 'options' => array( 'items' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'align', 'gap', 'marker' ) ),
			array( 'id' => 'look', 'label' => __( 'Look', 'fw' ), 'tab' => 'style', 'options' => array( 'design', 'shape', 'size', 'hover', 'tag_color' ) ),
		);
	}
}
