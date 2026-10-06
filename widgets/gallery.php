<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Gallery — media-library or post-thumbnail source, every layout (grid, masonry, justified,
 * carousel, slideshow, showcase, coverflow, marquee…), click behaviour, captions, presets.
 *
 * Not exposed: a grid's per-column ratio split (`col_ratio`, a split-slider) — carried in
 * the residue when set in the page builder.
 */
class FW_Elementor_Widget_Gallery extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'gallery';
	}

	public function get_title() {
		return __( 'Gallery', 'fw' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return array( 'gallery', 'images', 'photos', 'masonry', 'carousel', 'lightbox', 'slideshow' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'source', 'label' => __( 'Images', 'fw' ), 'tab' => 'content', 'options' => array( 'source' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design_settings', 'container_type' ) ),
			array( 'id' => 'behaviour', 'label' => __( 'Click & Captions', 'fw' ), 'tab' => 'content', 'options' => array( 'click', 'captions', 'caption_source' ) ),
			array( 'id' => 'look', 'label' => __( 'Images', 'fw' ), 'tab' => 'style', 'options' => array( 'image_style', 'box_style', 'rounded', 'hover_zoom' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'bg_color', 'caption_color' ) ),
		);
	}

	protected function control_overrides() {
		$placeholder = class_exists( '\Elementor\Utils' ) ? \Elementor\Utils::get_placeholder_image_src() : '';

		return array(
			'source/media/images' => array(
				'default' => $placeholder ? array_fill( 0, 6, array( 'id' => '', 'url' => $placeholder ) ) : array(),
			),
		);
	}
}
