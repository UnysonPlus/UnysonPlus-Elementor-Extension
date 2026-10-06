<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Parallax scene — depth layers that move with scroll or the pointer. */
class FW_Elementor_Widget_Parallax_Scene extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'parallax_scene';
	}

	public function get_title() {
		return __( 'Parallax Scene', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-parallax';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( 'parallax', 'layers', 'depth', 'scene', 'motion' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'layers', 'label' => __( 'Layers', 'fw' ), 'tab' => 'content', 'options' => array( 'layers' ) ),
			array( 'id' => 'motion', 'label' => __( 'Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'source', 'intensity', 'hold', 'pass_clicks' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'placement', 'height' ) ),
		);
	}

	protected function control_overrides() {
		$layer = function ( $anchor, $width, $depth, $z ) {
			return array( 'image' => $this->placeholder_image(), 'h_anchor' => $anchor, 'width' => $width, 'depth' => $depth, 'z' => $z );
		};
		return array(
			'layers' => array(
				'title_field' => '{{{ h_anchor }}} — depth {{{ depth }}}',
				'default'     => array( $layer( 'left', 45, 15, 1 ), $layer( 'center', 35, 35, 2 ), $layer( 'right', 30, 60, 3 ) ),
			),
		);
	}
}
