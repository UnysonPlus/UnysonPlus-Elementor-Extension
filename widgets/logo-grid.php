<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Logo grid — grid, boxed, carousel and marquee layouts, grayscale hover, labels. */
class FW_Elementor_Widget_Logo_Grid extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'logo_grid';
	}

	public function get_title() {
		return __( 'Logo Grid', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-logo';
	}

	public function get_keywords() {
		return array( 'logo', 'logos', 'clients', 'partners', 'marquee', 'brands' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'logos', 'label' => __( 'Logos', 'fw' ), 'tab' => 'content', 'options' => array( 'logos' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'columns', 'gap', 'logo_height', 'grayscale', 'show_labels' ) ),
			array( 'id' => 'motion', 'label' => __( 'Motion', 'fw' ), 'tab' => 'content', 'options' => array( 'autoplay', 'speed', 'direction' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'box_bg' ) ),
		);
	}

	protected function control_overrides() {
		$logo = function ( $name ) {
			return array(
				'name' => $name,
				'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 40" role="img" aria-label="' . esc_attr( $name ) . '"><circle cx="20" cy="20" r="12" fill="currentColor"/><text x="40" y="27" font-family="sans-serif" font-size="20" font-weight="700" fill="currentColor">' . esc_html( $name ) . '</text></svg>',
			);
		};

		return array(
			'logos' => array(
				'title_field' => '{{{ name || "Logo" }}}',
				'default'     => array( $logo( 'Northwind' ), $logo( 'Lumen' ), $logo( 'Arcadia' ), $logo( 'Halcyon' ) ),
			),
		);
	}
}
