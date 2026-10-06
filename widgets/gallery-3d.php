<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Motion gallery — 3D ring, panorama wall, card sphere, orbit, scatter, device cycler and more, with entrance motion. Not exposed: the box shadow (kept in the residue). */
class FW_Elementor_Widget_Gallery_3d extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'gallery_3d';
	}

	public function get_title() {
		return __( 'Motion Gallery', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-gallery-justified';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( '3d', 'gallery', 'ring', 'sphere', 'carousel', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'images', 'label' => __( 'Images', 'fw' ), 'tab' => 'content', 'options' => array( 'source' ) ),
			array( 'id' => 'design', 'label' => __( 'Design', 'fw' ), 'tab' => 'content', 'options' => array( 'design_settings', 'as_background' ) ),
			array( 'id' => 'entrance', 'label' => __( 'Entrance', 'fw' ), 'tab' => 'content', 'options' => array( 'entrance', 'entrance_stagger', 'entrance_distance', 'entrance_exit' ) ),
			array( 'id' => 'click', 'label' => __( 'Click & Captions', 'fw' ), 'tab' => 'content', 'options' => array( 'click', 'captions', 'caption_source' ) ),
			array( 'id' => 'style', 'label' => __( 'Box', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'source/media/images' => array( 'default' => array_fill( 0, 8, $this->placeholder_image() ) ),
		);
	}
}
