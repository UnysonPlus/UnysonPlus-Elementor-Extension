<?php
/**
 * Shortcode atts <-> Elementor widget settings — the contract the Site Converter relies on.
 *
 * For every builder node in the Site Converter snapshots whose shortcode has a widget:
 *
 *   1. ROUND TRIP — atts -> settings_from_atts() -> build_atts() gives back exactly the atts
 *      (strict types; key order is not significant).
 *   2. SAME MARKUP — the shortcode rendered from the atts and an Elementor element rendered
 *      from the settings produce identical HTML. Each side renders in its own PHP process:
 *      some views print one-time per-request output (an SVG sprite, a style block), so two
 *      renders in one request would differ for reasons unrelated to the conversion.
 *   3. PROGRAMMATIC DOCUMENT — a page written with Document::save() (never opened in the
 *      editor) renders the widget on the front end, with the design stylesheet in <head>.
 *
 * Run (on an install with Elementor + this extension active):
 *   php D:/xampp/wp-cli.phar --path=D:/xampp/htdocs/elementor eval-file <this file>
 */

if ( ! function_exists( 'fw_ext' ) || ! fw_ext( 'elementor' ) || ! did_action( 'elementor/loaded' ) ) {
	fwrite( STDERR, "FAIL: needs Elementor and the Unyson+ elementor extension active\n" );
	exit( 1 );
}

$snapshots = dirname( __DIR__, 2 ) . '/site-converter/tests/snapshots';

/** Every [ label, tag, atts ] builder node with a widget, across the snapshots. */
function up_rt_nodes( $snapshots ) {
	$ext   = fw_ext( 'elementor' );
	$nodes = array();

	foreach ( glob( $snapshots . '/*.json' ) as $file ) {
		$json  = json_decode( file_get_contents( $file ), true );
		$pages = isset( $json['files']['pages.json'] ) ? $json['files']['pages.json'] : null;
		$pages = is_string( $pages ) ? json_decode( $pages, true ) : $pages;
		$i     = 0;

		$walk = function ( $x ) use ( &$walk, &$nodes, &$i, $ext, $file ) {
			if ( ! is_array( $x ) ) {
				return;
			}
			if ( isset( $x['shortcode'], $x['atts'] ) && is_array( $x['atts'] ) && '' !== $ext->widget_for_shortcode( $x['shortcode'] ) ) {
				$nodes[] = array( basename( $file, '.json' ) . '#' . ( ++$i ) . ' ' . $x['shortcode'], $x['shortcode'], $x['atts'] );
			}
			foreach ( $x as $v ) {
				$walk( $v );
			}
		};
		$walk( $pages );
	}

	// Every widget at its shortcode's declared defaults — the full page-builder value shape,
	// so every exposed option type round-trips even for elements no snapshot contains. The
	// unique id is pinned: the defaults generate a random one, and both render processes
	// must see the same atts.
	foreach ( array_keys( $ext->get_widget_files() ) as $tag ) {
		if ( '' === $ext->widget_for_shortcode( $tag ) ) {
			continue;
		}
		$atts = fw_get_options_values_from_input( fw_ext( 'shortcodes' )->get_shortcode( $tag )->get_options(), array() );
		if ( is_array( $atts ) ) {
			$atts['unique_id'] = 'rt-' . str_replace( '_', '-', $tag );
			if ( 'contact_form' === $tag ) {
				$atts['id'] = 'rt-form'; // a `unique` option: random per call otherwise
			}
			$nodes[] = array( 'defaults ' . $tag, $tag, $atts );
		}
	}

	// A populated form: the forms extension's own Contact starter (header, two half-width
	// fields, a message, a consent box — each carrying options the panel does not show).
	if ( '' !== $ext->widget_for_shortcode( 'contact_form' ) && class_exists( 'FW_Forms_Starters' ) ) {
		$starters = FW_Forms_Starters::_filter_predefined( array() );
		if ( ! empty( $starters['fw-forms-contact']['json'] ) ) {
			$atts              = fw_get_options_values_from_input( fw_ext( 'shortcodes' )->get_shortcode( 'contact_form' )->get_options(), array() );
			$atts['unique_id'] = 'rt-form-data';
			$atts['id']        = 'rt-form-data';
			$atts['form']      = array( 'json' => $starters['fw-forms-contact']['json'] );
			$nodes[]           = array( 'contact starter form', 'contact_form', $atts );
		}
	}

	// A populated table: header + body rows, and one row with a merged cell (rides in the
	// row's residue).
	if ( '' !== $ext->widget_for_shortcode( 'table' ) ) {
		$atts = fw_get_options_values_from_input( fw_ext( 'shortcodes' )->get_shortcode( 'table' )->get_options(), array() );
		$cell = function ( $t, $extra = array() ) {
			return array_merge( array( 'textarea' => $t, 'amount' => '', 'description' => '', 'switch' => 'no', 'button' => '' ), $extra );
		};
		$atts['unique_id'] = 'rt-table-data';
		$atts['table']     = array(
			'header_options' => array( 'table_purpose' => 'tabular', 'header_rows' => 1, 'footer_rows' => 0 ),
			'cols'           => array( array( 'name' => 'default-col' ), array( 'name' => 'desc-col' ), array( 'name' => 'default-col' ) ),
			'rows'           => array( array( 'name' => 'heading-row' ), array( 'name' => 'default-row' ), array( 'name' => 'default-row' ) ),
			'content'        => array(
				array( $cell( 'Plan' ), $cell( 'Storage' ), $cell( 'Support' ) ),
				array( $cell( 'Starter' ), $cell( '5 GB' ), $cell( 'Email' ) ),
				array( $cell( 'Pro', array( 'colspan' => 2 ) ), $cell( '', array( 'merged' => true ) ), $cell( "Priority\nphone" ) ),
			),
		);
		$nodes[] = array( 'data table', 'table', $atts );
	}

	return $nodes;
}

function up_rt_ksort( $v ) {
	if ( ! is_array( $v ) ) {
		return $v;
	}
	if ( array_keys( $v ) !== range( 0, count( $v ) - 1 ) ) {
		ksort( $v );
	}
	return array_map( 'up_rt_ksort', $v );
}

function up_rt_render( $mode, array $nodes ) {
	$out = array();

	foreach ( $nodes as $n => $node ) {
		list( , $tag, $atts ) = $node;
		$widget = fw_ext( 'elementor' )->get_widget( $tag );

		if ( 'shortcode' === $mode ) {
			$shortcode = fw_ext( 'shortcodes' )->get_shortcode( $tag );
			$out[ $n ] = $shortcode->render( $atts );
			continue;
		}

		$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( array(
			'id'         => 'rt' . $n,
			'elType'     => 'widget',
			'widgetType' => $widget->get_name(),
			'settings'   => $widget->settings_from_atts( $atts ),
		) );
		ob_start();
		$element->render_content();
		$out[ $n ] = ob_get_clean();
	}

	return $out;
}

$nodes = up_rt_nodes( $snapshots );

// Child process: render one side and hand it back.
if ( getenv( 'UP_RT_MODE' ) ) {
	echo "\n@@UP_RT@@" . base64_encode( wp_json_encode( up_rt_render( getenv( 'UP_RT_MODE' ), $nodes ) ) );
	return;
}

$pass = 0;
$fail = 0;
$check = function ( $label, $ok, $detail = '' ) use ( &$pass, &$fail ) {
	if ( $ok ) {
		$pass++;
		echo "  PASS  $label\n";
		return;
	}
	$fail++;
	echo "  FAIL  $label" . ( '' !== $detail ? "\n        $detail" : '' ) . "\n";
};

echo "Nodes with a widget: " . count( $nodes ) . "\n";
$check( 'the snapshots contain nodes with a widget', count( $nodes ) > 0 );

// 1. Round trip.
foreach ( $nodes as $node ) {
	list( $label, $tag, $atts ) = $node;
	$widget   = fw_ext( 'elementor' )->get_widget( $tag );
	$settings = FW_Elementor_Option_Bridge::settings_from_atts( $tag, $atts );
	$back     = $widget->build_atts( $settings );

	// build_atts() lays the shortcode defaults underneath; compare only what the node set,
	// and check separately that nothing it set was replaced.
	$back = array_intersect_key( $back, $atts );
	$same = up_rt_ksort( $back ) === up_rt_ksort( $atts );
	$check( "round trip  $label", $same, $same ? '' : 'residue: ' . wp_json_encode( FW_Elementor_Option_Bridge::residue( up_rt_ksort( $atts ), up_rt_ksort( $back ) ) ) );

	// Every exposed option the atts carry must reach the panel as a control value (its
	// own name, or `<name>__<part>` for a composite) rather than only the residue.
	$sections = new ReflectionMethod( $widget, 'sections' );
	$sections->setAccessible( true );
	$missed = array();
	foreach ( $sections->invoke( $widget ) as $section ) {
		foreach ( $section['options'] as $id ) {
			if ( ! array_key_exists( $id, $atts ) ) {
				continue;
			}
			$name  = FW_Elementor_Option_Bridge::name( $id );
			$found = false;
			foreach ( array_keys( $settings ) as $k ) {
				if ( $k === $name || 0 === strpos( $k, $name . '__' ) ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$missed[] = $id;
			}
		}
	}
	$check( "settings carry real controls  $label", ! $missed, 'not in the panel: ' . implode( ', ', $missed ) );
}

// 2. Same markup, each side in its own process.
$sides = array();
foreach ( array( 'shortcode', 'widget' ) as $mode ) {
	$cmd = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( $_SERVER['argv'][0] )
		. ' --path=' . escapeshellarg( ABSPATH ) . ' eval-file ' . escapeshellarg( __FILE__ );
	putenv( 'UP_RT_MODE=' . $mode );
	$raw = (string) shell_exec( $cmd );
	putenv( 'UP_RT_MODE' );
	$at  = strrpos( $raw, '@@UP_RT@@' );
	$sides[ $mode ] = false === $at ? null : json_decode( base64_decode( substr( $raw, $at + 9 ) ), true );
	$check( "rendered the $mode side", is_array( $sides[ $mode ] ), substr( $raw, 0, 300 ) );
}

if ( is_array( $sides['shortcode'] ) && is_array( $sides['widget'] ) ) {
	foreach ( $nodes as $n => $node ) {
		$a = isset( $sides['shortcode'][ $n ] ) ? $sides['shortcode'][ $n ] : null;
		$b = isset( $sides['widget'][ $n ] ) ? $sides['widget'][ $n ] : null;
		// Outside a page (no <head> pass) the widget prints its page CSS — the element's own
		// Custom CSS and off-scale spacing rules — in a <style> ahead of the markup. The page
		// builder writes the same rules to the page stylesheet, so compare the markup without it.
		$style = '';
		if ( is_string( $b ) && preg_match( '#^\s*<style>(.*?)</style>#s', $b, $sm ) ) {
			$style = $sm[1];
			$b     = substr( trim( $b ), strlen( trim( $sm[0] ) ) );
		}
		$want = FW_Elementor_Shortcode_Widget::page_css( array( $node[2] ) );
		if ( '' !== $want || '' !== $style ) {
			$check( 'page CSS     ' . $node[0], '' !== $style && ( false === strpos( $want, '.u' ) || preg_match( '/\.u[0-9a-z]+/', $style ) ), 'want: ' . substr( $want, 0, 120 ) . "
        got:  " . substr( $style, 0, 120 ) );
		}
		$detail = '';
		if ( is_string( $a ) && is_string( $b ) && trim( $a ) !== trim( $b ) ) {
			$a = trim( $a );
			$b = trim( $b );
			$at     = strspn( $a ^ $b, "\0" );
			$detail = 'first difference at byte ' . $at . ":\n        shortcode: " . substr( $a, max( 0, $at - 60 ), 160 ) . "\n        widget:    " . substr( $b, max( 0, $at - 60 ), 160 );
		}
		// Elementor indents its own output around a widget; the widget's markup is what must match.
		// An element with an empty list renders nothing on both sides (the defaults nodes);
		// identical-and-empty still proves the two paths agree.
		$same = is_string( $a ) && is_string( $b ) && trim( $a ) === trim( $b );
		$check( 'same markup  ' . $node[0] . ( $same && '' === trim( $a ) ? '  (both empty)' : '' ), $same, $detail );
	}
}

// 3. A document written programmatically, never opened in the editor.
$testimonials = array_values( array_filter( $nodes, function ( $n ) { return 'testimonials' === $n[1]; } ) );
if ( $testimonials ) {
	list( , $tag, $atts ) = $testimonials[0];
	$widget = fw_ext( 'elementor' )->get_widget( $tag );
	$slug   = 'up-elementor-round-trip';
	$page   = get_page_by_path( $slug );
	$id     = $page ? $page->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Unyson+ Elementor round trip', 'post_name' => $slug ) );

	$atts['design_settings']['design'] = 'masonry'; // a design with its own stylesheet
	wp_set_current_user( 1 ); // Document::save() checks the user may edit the post
	$document = \Elementor\Plugin::$instance->documents->get( $id, false );
	// Document::save() does not flag the post as built with Elementor (the editor does);
	// without it the theme renders post_content and the Elementor data is never used.
	$document->set_is_built_with_elementor( true );
	$document->save( array(
		'elements' => array(
			array(
				'id'       => 'rtc0001',
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => 'rtw0001',
						'elType'     => 'widget',
						'widgetType' => $widget->get_name(),
						'settings'   => $widget->settings_from_atts( $atts ),
						'elements'   => array(),
					),
				),
			),
		),
		'settings' => array( 'template' => 'elementor_header_footer' ),
	) );

	$saved = (string) get_post_meta( $id, '_elementor_data', true );
	$check( 'Document::save kept the widget', false !== strpos( $saved, '"widgetType":"' . $widget->get_name() . '"' ) );
	$check( 'Document::save kept the residue setting', false !== strpos( $saved, FW_Elementor_Option_Bridge::EXTRA ) );

	$html = wp_remote_retrieve_body( wp_remote_get( get_permalink( $id ), array( 'timeout' => 60 ) ) );
	$head = substr( $html, 0, (int) strpos( $html, '</head>' ) );
	$check( 'front end renders the widget', false !== strpos( $html, 'elementor-widget-' . $widget->get_name() ) && false !== strpos( $html, 'design-masonry' ) );
	$check( 'design stylesheet is in <head>', false !== strpos( $head, 'design-masonry' ) || false !== strpos( $head, 'asset-optimizer/combined-' ) );
}

echo "\n$pass passed, $fail failed\n";
if ( $fail ) {
	exit( 1 );
}
