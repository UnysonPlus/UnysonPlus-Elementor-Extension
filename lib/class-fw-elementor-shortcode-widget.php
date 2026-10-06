<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * An Elementor widget backed by a Unyson+ shortcode.
 *
 * A concrete widget only DECLARES: which shortcode, and which of its options appear in
 * which panel section. The controls are generated from the shortcode's own options.php
 * by FW_Elementor_Option_Bridge, and render() hands the saved values back to the
 * shortcode — so the widget renders through the same view as the page builder and the
 * block editor, and a fix to the element reaches all three.
 *
 * Elementor's own Advanced tab (margin, padding, CSS id / classes, motion effects,
 * responsive visibility) applies to the widget wrapper, so the shortcode's Advanced and
 * Animation tabs are deliberately not exposed — two controls for one job would only
 * disagree with each other.
 *
 * Loaded on `elementor/widgets/register`, after Elementor's classes exist.
 */
abstract class FW_Elementor_Shortcode_Widget extends \Elementor\Widget_Base {

	/** @var array tag => option leaves, shared by every instance of a widget type. */
	private static $leaves_cache = array();

	/** The shortcode tag this widget renders (underscored, e.g. 'testimonials'). */
	abstract public function shortcode_tag();

	/**
	 * The panel layout: a list of sections, each
	 * `[ 'id' => …, 'label' => …, 'tab' => 'content'|'style', 'options' => [ option ids ] ]`.
	 */
	abstract protected function sections();

	/** The first non-empty choice of a select option — a starter pick (the first product). */
	protected function first_choice( $id ) {
		$leaves = $this->leaves();

		foreach ( (array) ( isset( $leaves[ $id ]['choices'] ) ? $leaves[ $id ]['choices'] : array() ) as $key => $label ) {
			if ( '' !== (string) $key ) {
				return (string) $key;
			}
		}

		return '';
	}

	/** The panel note for an element with no options of its own. */
	protected function no_options_note() {
		return __( 'This element has no settings of its own; it follows the site\'s settings. Use the Advanced tab for spacing and visibility.', 'fw' );
	}

	/**
	 * Per-path partial control args merged over the generated ones — a friendlier
	 * default, a repeater's title_field, a shorter label. Keyed by option PATH.
	 */
	protected function control_overrides() {
		return array();
	}

	/**
	 * Elementor's placeholder image as a MEDIA value — starter content for a widget that
	 * renders nothing without an image (a new widget should show what it is).
	 */
	protected function placeholder_image() {
		return array(
			'id'  => '',
			'url' => class_exists( '\Elementor\Utils' ) ? \Elementor\Utils::get_placeholder_image_src() : '',
		);
	}

	/**
	 * The panel icon: the element's own pixel glyph (its static/img/page_builder.svg, the
	 * same one the page builder shows) drawn as a mask in the panel's text colour — see the
	 * editor stylesheet in FW_Extension_Elementor::_action_editor_icons(). An element
	 * without one keeps its Elementor icon.
	 */
	public function get_icon() {
		return '' !== $this->icon_uri() ? 'up-icon up-icon--' . $this->get_name() : $this->fallback_icon();
	}

	/** An Elementor icon class, for an element that ships no glyph of its own. */
	protected function fallback_icon() {
		return 'eicon-apps';
	}

	/** URL of the element's page_builder.svg, or ''. */
	public function icon_uri() {
		$shortcode = $this->get_shortcode();
		$uri       = $shortcode ? $shortcode->locate_URI( '/static/img/page_builder.svg' ) : false;

		return is_string( $uri ) ? $uri : '';
	}

	public function get_name() {
		return 'up-' . str_replace( '_', '-', $this->shortcode_tag() );
	}

	public function get_categories() {
		return array( 'unysonplus' );
	}

	public function get_script_depends() {
		return array( 'fw-elementor-widgets' );
	}

	/** Markup stays lean: the shortcode draws its own wrapper. */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/** @return FW_Shortcode|null */
	public function get_shortcode() {
		$shortcodes = fw_ext( 'shortcodes' );
		return $shortcodes ? $shortcodes->get_shortcode( $this->shortcode_tag() ) : null;
	}

	protected function leaves() {
		$tag = $this->shortcode_tag();

		if ( ! isset( self::$leaves_cache[ $tag ] ) ) {
			$shortcode                  = $this->get_shortcode();
			self::$leaves_cache[ $tag ] = $shortcode ? FW_Elementor_Option_Bridge::leaves( $shortcode->get_options() ) : array();
		}

		return self::$leaves_cache[ $tag ];
	}

	protected function exposed_ids() {
		$ids = array();
		foreach ( $this->sections() as $section ) {
			$ids = array_merge( $ids, $section['options'] );
		}
		return $ids;
	}

	protected function register_controls() {
		$leaves    = $this->leaves();
		$overrides = $this->control_overrides();
		$sections  = $this->sections();

		// An element with no options of its own (a cart, a checkout) still needs a section:
		// the residue control lives in the first one, and the panel should say why it is empty.
		if ( ! $sections ) {
			$this->start_controls_section( 'section_settings', array(
				'label' => __( 'Settings', 'fw' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			) );
			$this->add_control( FW_Elementor_Option_Bridge::EXTRA, array( 'type' => \Elementor\Controls_Manager::HIDDEN, 'default' => '' ) );
			$this->add_control( 'up_no_options', array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => esc_html( $this->no_options_note() ),
			) );
			$this->end_controls_section();
			return;
		}

		foreach ( $sections as $i => $section ) {
			$this->start_controls_section( 'section_' . $section['id'], array(
				'label' => $section['label'],
				'tab'   => 'style' === $section['tab'] ? \Elementor\Controls_Manager::TAB_STYLE : \Elementor\Controls_Manager::TAB_CONTENT,
			) );

			if ( 0 === $i ) {
				// The atts no control carries (see FW_Elementor_Option_Bridge::EXTRA).
				$this->add_control( FW_Elementor_Option_Bridge::EXTRA, array(
					'type'    => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
				) );
			}

			FW_Elementor_Option_Bridge::add_controls( $this, $leaves, $section['options'], $overrides );

			$this->end_controls_section();
		}
	}

	/**
	 * Page-builder atts -> this widget's settings. Exposed options become control values;
	 * everything else (unexposed options, legacy keys, values a control would retype) is
	 * kept as the residue, so build_atts() of the result gives back $atts.
	 *
	 * @param array $atts Atts in the shape the page builder stores for this shortcode.
	 * @return array
	 */
	public function settings_from_atts( array $atts ) {
		$settings = FW_Elementor_Option_Bridge::to_settings( $atts, $this->leaves(), $this->exposed_ids() );
		$rebuilt  = FW_Elementor_Option_Bridge::to_atts( $settings, $this->leaves(), $this->exposed_ids() );
		$residue  = FW_Elementor_Option_Bridge::residue( $atts, $rebuilt );

		$settings[ FW_Elementor_Option_Bridge::EXTRA ] = null === $residue ? '' : wp_json_encode( $residue, JSON_PRESERVE_ZERO_FRACTION );

		return $settings;
	}

	/**
	 * The full atts for a settings array: the widget's values over the shortcode's
	 * declared defaults. Elementor saves only what differs from a control's default, so
	 * $settings must already have the control defaults merged in (get_settings_for_display()
	 * does; see with_control_defaults() for raw saved data).
	 */
	public function build_atts( array $settings ) {
		$shortcode = $this->get_shortcode();
		$atts      = FW_Elementor_Option_Bridge::to_atts( $settings, $this->leaves(), $this->exposed_ids() );
		$residue   = isset( $settings[ FW_Elementor_Option_Bridge::EXTRA ] ) ? FW_Elementor_Option_Bridge::decode_residue( $settings[ FW_Elementor_Option_Bridge::EXTRA ] ) : null;

		if ( null !== $residue ) {
			$atts = FW_Elementor_Option_Bridge::merge_residue( $atts, $residue );
		}

		$defaults = array();

		if ( $shortcode ) {
			$defaults = fw_get_options_values_from_input( $shortcode->get_options(), array() );
			$defaults = is_array( $defaults ) ? $defaults : array();
		}

		// A stable id per widget, so the scoped class the shortcode derives from it does
		// not change on every render. One carried in from page-builder atts wins: custom
		// CSS written against it must keep matching.
		if ( $this->get_id() ) {
			$defaults['unique_id'] = 'el' . $this->get_id();
		}

		return array_merge( $defaults, $atts );
	}

	/** Raw saved settings with every control's default filled in underneath. */
	public function with_control_defaults( array $settings ) {
		foreach ( $this->get_controls() as $name => $control ) {
			if ( ! array_key_exists( $name, $settings ) && array_key_exists( 'default', $control ) ) {
				$settings[ $name ] = $control['default'];
			}
		}
		return $settings;
	}

	/**
	 * Enqueue this element's assets for one saved instance — its static.php set plus the
	 * stylesheet of the design it uses. Called from the document-level pass in <head>, so
	 * the styles print before the markup rather than after it.
	 */
	public function enqueue_for_settings( array $settings ) {
		$shortcode = $this->get_shortcode();

		if ( ! $shortcode ) {
			return;
		}

		$shortcode->_enqueue_static();

		$atts = $this->build_atts( $this->with_control_defaults( $settings ) );

		$this->enqueue_instance_static( $atts );

		if ( function_exists( 'fw_sc_design_resolve' ) ) {
			$this->enqueue_design( fw_sc_design_resolve( $this->shortcode_tag(), $atts, 'default' ) );
		}
	}

	/**
	 * Fire the shortcode's per-instance asset action for one set of atts.
	 *
	 * The page builder fires `fw_ext_shortcodes_enqueue_static:<tag>` for every instance it
	 * finds in the content, with that instance's ENCODED attribute string, and shortcodes
	 * hang their atts-dependent assets on it (Logo Grid's carousel loads Splide and its
	 * mount script only there). An Elementor page has no shortcode content to scan, so
	 * the widget fires the same action, encoded by the page builder's own JSON coder.
	 */
	public function enqueue_instance_static( array $atts ) {
		$shortcodes = fw_ext( 'shortcodes' );
		$coder      = $shortcodes ? $shortcodes->get_attr_coder( 'json' ) : null;

		if ( ! $coder ) {
			return;
		}

		global $post;

		$encoded = $coder->encode( $atts, $this->shortcode_tag(), $post ? $post->ID : 0 );

		if ( ! is_array( $encoded ) ) {
			return;
		}

		$string = '';
		foreach ( $encoded as $key => $value ) {
			$string .= ' ' . $key . '="' . $value . '"';
		}

		do_action( 'fw_ext_shortcodes_enqueue_static:' . $this->shortcode_tag(), array(
			'atts_string' => $string,
			'post'        => $post,
		) );
	}

	/**
	 * For the editor preview: the per-instance assets of every choice the panel can switch
	 * to. Each picker-like option exposed by the widget (a design, a layout) is tried with
	 * each of its choices over the defaults, so whatever the user picks already has its
	 * script and stylesheet loaded — re-renders arrive over AJAX and cannot enqueue.
	 */
	public function enqueue_preview_static() {
		$defaults = $this->build_atts( $this->with_control_defaults( array() ) );
		$leaves   = $this->leaves();

		$this->enqueue_instance_static( $defaults );

		foreach ( $this->exposed_ids() as $id ) {
			if ( ! isset( $leaves[ $id ] ) ) {
				continue;
			}

			$option = $leaves[ $id ];
			$path   = $id;

			if ( 'multi-picker' === $option['type'] && ! empty( $option['picker'] ) && is_array( $option['picker'] ) ) {
				$path   = $id . '/' . key( $option['picker'] );
				$option = current( $option['picker'] );
			}

			if ( ! in_array( $option['type'], array( 'image-picker', 'select', 'short-select' ), true ) || empty( $option['choices'] ) || ! is_array( $option['choices'] ) ) {
				continue;
			}

			foreach ( array_keys( $option['choices'] ) as $choice ) {
				$variant = $defaults;
				fw_aks( $path, $choice, $variant );
				$this->enqueue_instance_static( $variant );
			}
		}
	}

	/**
	 * One design's stylesheet and script. The stylesheet goes through the same helper the
	 * shortcode's view calls at render time, under the same handle, so the two dedupe
	 * instead of loading the file twice. That helper does not handle a design's script,
	 * so it is enqueued here the way the page builder's per-instance pass does it.
	 */
	public function enqueue_design( $design ) {
		$tag = $this->shortcode_tag();

		if ( function_exists( 'fw_sc_design_enqueue_now' ) ) {
			fw_sc_design_enqueue_now( $tag, $design );
		}

		$designs = function_exists( 'fw_sc_designs' ) ? fw_sc_designs( $tag ) : array();

		if ( ! empty( $designs[ $design ]['js_uri'] ) ) {
			$base = 'fw-shortcode-' . str_replace( '_', '-', $tag );
			wp_enqueue_script(
				$base . '-' . $design,
				$designs[ $design ]['js_uri'],
				array_merge( array_values( (array) $designs[ $design ]['vendor_deps'] ), array( $base ) ),
				fw_ext( 'shortcodes' )->manifest->get_version(),
				true
			);
		}
	}

	protected function render() {
		$shortcode = $this->get_shortcode();

		if ( ! $shortcode ) {
			return;
		}

		$atts = $this->build_atts( $this->get_settings_for_display() );

		// Covers placements the <head> pass cannot see (a template, a popup); deduped by
		// handle, so it is free when the head pass already ran.
		$shortcode->_enqueue_static();
		$this->enqueue_instance_static( $atts );

		echo $shortcode->render( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput -- the shortcode view escapes its own output
	}
}
