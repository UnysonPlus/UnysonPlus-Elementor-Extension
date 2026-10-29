<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * The builder-neutral DESIGN model — the half of every sync that speaks Unyson+.
 *
 * read() lifts the syncable parts of Unyson+ Theme Settings into plain values (hex colours, pixel
 * sizes, unitless line heights); write() puts a (partial) design back. A provider only ever maps this
 * shape onto its builder's settings, so a new builder never has to learn how Unyson+ stores anything.
 *
 *   colors      [ { slug, name, color } ]            ← theme_colors (Color Presets)
 *   text_color  '#hex'                              ← typography.body.color
 *   link        '#hex' · link_hover '#hex'           ← typography.body_link / body_link_hover
 *   fonts       { heading: { family }, body: Style } ← typography.heading_font / typography.body
 *   headings    { h1 … h6: Style }                   ← typography.h1 … h6
 *   container   { lg: px, md: px }                   ← layout_container_width
 *   background  '#hex'                              ← site_background (its colour layer)
 *   button      { text, bg, border, border_width, radius, hover_text, hover_bg, hover_border }
 *                                                    ← the primary Button Preset (button_colors)
 *   text_styles [ { name, Style } ]                  ← font_sizes (Text Styles)
 *
 *   Style = { family, weight, size (px), line_height (unitless), letter_spacing (px), transform, color }
 *
 * Every field is optional: an empty value means "not set", and write() leaves unset fields untouched,
 * so a builder that cannot express something never erases it on the Unyson+ side.
 */
final class FW_Builder_Sync_Design {

	/** General → Layout keeps container width and site background in its own group, where the theme reads them. */
	const LAYOUT = 'general_layout/';

	/* ------------------------------------------------------------------ *
	 * Read
	 * ------------------------------------------------------------------ */

	/** @return array the current design */
	public static function read() {
		$typo = (array) self::get( 'typography', array() );

		$design = array(
			'colors'      => self::read_colors(),
			'text_color'  => self::color( $typo['body']['color'] ?? '' ),
			'link'        => self::color( $typo['body_link'] ?? '' ),
			'link_hover'  => self::color( $typo['body_link_hover'] ?? '' ),
			'fonts'       => array(
				'heading' => array( 'family' => trim( (string) ( $typo['heading_font']['family'] ?? '' ) ) ),
				'body'    => self::style_from_typo( (array) ( $typo['body'] ?? array() ) ),
			),
			'headings'    => array(),
			'container'   => self::read_container(),
			'background'  => self::read_background(),
			'button'      => self::read_button(),
			'text_styles' => self::read_text_styles(),
		);
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
			if ( ! empty( $typo[ $h ] ) && is_array( $typo[ $h ] ) ) {
				$design['headings'][ $h ] = self::style_from_typo( $typo[ $h ] );
			}
		}
		return $design;
	}

	private static function read_colors() {
		$rows = function_exists( 'unysonplus_get_color_presets' ) ? (array) unysonplus_get_color_presets() : (array) self::get( 'theme_colors', array() );
		$out  = array();
		foreach ( $rows as $r ) {
			if ( ! is_array( $r ) || empty( $r['name'] ) || empty( $r['color'] ) ) { continue; }
			$out[] = array( 'slug' => self::slug( $r['name'] ), 'name' => (string) $r['name'], 'color' => self::color( $r['color'] ) );
		}
		return $out;
	}

	private static function read_container() {
		$v   = self::get( self::LAYOUT . 'layout_container_width', array() );
		$out = array();
		foreach ( array( 'lg', 'md' ) as $bp ) {
			if ( isset( $v[ $bp ]['value'] ) && 'px' === ( $v[ $bp ]['unit'] ?? 'px' ) && (float) $v[ $bp ]['value'] > 0 ) {
				$out[ $bp ] = (float) $v[ $bp ]['value'];
			}
		}
		return $out;
	}

	private static function read_background() {
		$v = self::get( self::LAYOUT . 'site_background', array() );
		if ( ! is_array( $v ) ) { return ''; }
		// background-pro keeps its colour layer under `color` (a compact colour or a plain value).
		foreach ( array( array( 'color' ), array( 'background_color' ), array( 'color', 'value' ) ) as $path ) {
			$cur = $v;
			foreach ( $path as $k ) { $cur = is_array( $cur ) && isset( $cur[ $k ] ) ? $cur[ $k ] : null; }
			$c = self::color( $cur );
			if ( '' !== $c ) { return $c; }
		}
		return '';
	}

	/** The Button Preset the builder's global button should mirror: role/slug "primary", else the first. */
	private static function primary_button_index( array $rows ) {
		foreach ( $rows as $i => $r ) {
			if ( ! is_array( $r ) ) { continue; }
			if ( 'primary' === ( $r['role'] ?? '' ) || 'primary' === self::slug( $r['color_name'] ?? '' ) ) { return $i; }
		}
		foreach ( $rows as $i => $r ) { if ( is_array( $r ) ) { return $i; } }
		return null;
	}

	private static function read_button() {
		$rows = (array) self::get( 'button_colors', array() );
		$i    = self::primary_button_index( $rows );
		if ( null === $i ) { return array(); }
		$d = (array) ( $rows[ $i ]['states']['default'] ?? array() );
		$h = (array) ( $rows[ $i ]['states']['hover'] ?? array() );
		$bw = $d['border_width'] ?? '';
		return array_filter( array(
			'text'         => self::color( $d['text_color'] ?? '' ),
			'bg'           => self::color( $d['bg_color'] ?? '' ),
			'border'       => self::color( $d['border_color'] ?? '' ),
			'border_width' => is_array( $bw ) ? (float) ( $bw['value'] ?? 0 ) : (float) $bw,
			'hover_text'   => self::color( $h['text_color'] ?? '' ),
			'hover_bg'     => self::color( $h['bg_color'] ?? '' ),
			'hover_border' => self::color( $h['border_color'] ?? '' ),
		), function ( $v ) { return '' !== $v && 0.0 !== $v; } );
	}

	private static function read_text_styles() {
		$rows = function_exists( 'unysonplus_preset_store_get' ) ? unysonplus_preset_store_get( 'font_sizes', null ) : self::get( 'font_sizes', null );
		$out  = array();
		foreach ( (array) $rows as $r ) {
			if ( ! is_array( $r ) || '' === trim( (string) ( $r['name'] ?? '' ) ) ) { continue; }
			$size = $r['size'] ?? '';
			$out[] = array(
				'name'           => (string) $r['name'],
				'size'           => is_array( $size ) ? self::to_px( ( $size['value'] ?? '' ) . ( $size['unit'] ?? 'px' ) ) : self::to_px( $size ),
				'weight'         => (string) ( $r['weight'] ?? '' ),
				'line_height'    => self::unitless( $r['line_height'] ?? '' ),
				'letter_spacing' => self::ls_px( $r['letter_spacing'] ?? '', is_array( $size ) ? 0 : (float) $size ),
				'transform'      => (string) ( $r['transform'] ?? '' ),
				'color'          => self::color( $r['color'] ?? '' ),
			);
		}
		return $out;
	}

	/** A Unyson+ typography row → Style. */
	private static function style_from_typo( array $t ) {
		$size = $t['size'] ?? '';
		$px   = is_array( $size ) ? self::to_px( ( $size['value'] ?? '' ) . ( $size['unit'] ?? 'px' ) ) : self::to_px( $size );
		$var  = (string) ( $t['variation'] ?? '' );
		return array(
			'family'         => trim( (string) ( $t['family'] ?? '' ) ),
			'weight'         => ( 'regular' === $var ) ? '400' : ( preg_match( '/^\d{3}/', $var, $m ) ? $m[0] : '' ),
			'size'           => $px,
			'line_height'    => self::unitless( $t['line-height'] ?? '' ),
			'letter_spacing' => is_numeric( $t['letter-spacing'] ?? '' ) ? (float) $t['letter-spacing'] : self::to_px( $t['letter-spacing'] ?? '' ),
			'transform'      => (string) ( $t['text-transform'] ?? '' ),
			'color'          => self::color( $t['color'] ?? '' ),
		);
	}

	/* ------------------------------------------------------------------ *
	 * Write
	 * ------------------------------------------------------------------ */

	/**
	 * Merge a (partial) design into Unyson+ Theme Settings. Fields absent from $d are left alone.
	 *
	 * @param array $d       design fields to write
	 * @param array $renames old slug => new name, for colours the builder renamed (keeps the row in place)
	 * @return string[] the setting keys that changed
	 */
	public static function write( array $d, array $renames = array() ) {
		$changed = array();

		if ( isset( $d['colors'] ) && is_array( $d['colors'] ) && function_exists( 'unysonplus_preset_store_set' ) ) {
			$rows = function_exists( 'unysonplus_get_color_presets' ) ? (array) unysonplus_get_color_presets() : array();
			$new  = self::merge_colors( $rows, $d['colors'], $renames );
			if ( $new !== $rows ) { unysonplus_preset_store_set( 'theme_colors', $new ); $changed[] = 'theme_colors'; }
		}

		$typo = (array) self::get( 'typography', array() );
		$t0   = $typo;
		if ( isset( $d['text_color'] ) && '' !== $d['text_color'] ) { $typo['body']['color'] = $d['text_color']; }
		if ( isset( $d['link'] ) && '' !== $d['link'] ) { $typo['body_link'] = self::compact( $d['link'] ); }
		if ( isset( $d['link_hover'] ) && '' !== $d['link_hover'] ) { $typo['body_link_hover'] = self::compact( $d['link_hover'] ); }
		if ( ! empty( $d['fonts']['heading']['family'] ) ) { $typo['heading_font'] = array( 'family' => (string) $d['fonts']['heading']['family'] ); }
		if ( ! empty( $d['fonts']['body'] ) ) { $typo['body'] = self::typo_from_style( (array) ( $typo['body'] ?? array() ), $d['fonts']['body'] ); }
		foreach ( (array) ( $d['headings'] ?? array() ) as $h => $st ) {
			if ( ! preg_match( '/^h[1-6]$/', (string) $h ) || ! is_array( $st ) ) { continue; }
			$typo[ $h ] = self::typo_from_style( (array) ( $typo[ $h ] ?? array() ), $st );
		}
		if ( $typo !== $t0 ) { self::set( 'typography', $typo ); $changed[] = 'typography'; }

		if ( ! empty( $d['container'] ) && is_array( $d['container'] ) ) {
			$cw = (array) self::get( self::LAYOUT . 'layout_container_width', array() );
			$c0 = $cw;
			if ( ! isset( $cw['base'] ) ) { $cw['base'] = array( 'value' => '100', 'unit' => '%' ); }
			foreach ( array( 'lg', 'md' ) as $bp ) {
				if ( ! empty( $d['container'][ $bp ] ) ) { $cw[ $bp ] = array( 'value' => (string) round( (float) $d['container'][ $bp ] ), 'unit' => 'px' ); }
			}
			if ( $cw !== $c0 ) { self::set( self::LAYOUT . 'layout_container_width', $cw ); $changed[] = 'layout_container_width'; }
		}

		if ( ! empty( $d['background'] ) ) {
			$bg = self::get( self::LAYOUT . 'site_background', array() );
			$bg = is_array( $bg ) ? $bg : array();
			$b0 = $bg;
			$bg['color'] = self::compact( $d['background'] );
			if ( $bg !== $b0 ) { self::set( self::LAYOUT . 'site_background', $bg ); $changed[] = 'site_background'; }
		}

		if ( ! empty( $d['button'] ) && is_array( $d['button'] ) ) {
			$rows = (array) self::get( 'button_colors', array() );
			$i    = self::primary_button_index( $rows );
			if ( null !== $i ) {
				$r0  = $rows;
				$b   = $d['button'];
				$map = array( 'text' => array( 'default', 'text_color' ), 'bg' => array( 'default', 'bg_color' ), 'border' => array( 'default', 'border_color' ),
					'hover_text' => array( 'hover', 'text_color' ), 'hover_bg' => array( 'hover', 'bg_color' ), 'hover_border' => array( 'hover', 'border_color' ) );
				foreach ( $map as $k => $path ) {
					if ( empty( $b[ $k ] ) ) { continue; }
					if ( ! isset( $rows[ $i ]['states'][ $path[0] ] ) || ! is_array( $rows[ $i ]['states'][ $path[0] ] ) ) { $rows[ $i ]['states'][ $path[0] ] = array(); }
					if ( self::color( $rows[ $i ]['states'][ $path[0] ][ $path[1] ] ?? '' ) !== $b[ $k ] ) {
						$rows[ $i ]['states'][ $path[0] ][ $path[1] ] = self::compact( $b[ $k ] );
					}
				}
				if ( ! empty( $b['border_width'] ) ) { $rows[ $i ]['states']['default']['border_width'] = array( 'value' => (string) $b['border_width'], 'unit' => 'px' ); }
				if ( $rows !== $r0 ) { self::set( 'button_colors', $rows ); $changed[] = 'button_colors'; }
			}
		}

		if ( isset( $d['text_styles'] ) && is_array( $d['text_styles'] ) && function_exists( 'unysonplus_preset_store_set' ) ) {
			$rows = (array) unysonplus_preset_store_get( 'font_sizes', array() );
			$r0   = $rows;
			foreach ( $d['text_styles'] as $ts ) {
				if ( ! is_array( $ts ) || '' === trim( (string) ( $ts['name'] ?? '' ) ) ) { continue; }
				$found = null;
				foreach ( $rows as $i => $r ) { if ( is_array( $r ) && self::slug( $r['name'] ?? '' ) === self::slug( $ts['name'] ) ) { $found = $i; break; } }
				$row = null === $found ? array( 'name' => (string) $ts['name'] ) : $rows[ $found ];
				if ( ! empty( $ts['size'] ) ) { $row['size'] = (string) round( (float) $ts['size'], 2 ); }
				if ( isset( $ts['weight'] ) && '' !== $ts['weight'] ) { $row['weight'] = (string) $ts['weight']; }
				if ( ! empty( $ts['line_height'] ) ) { $row['line_height'] = (string) $ts['line_height']; }
				if ( isset( $ts['letter_spacing'] ) && '' !== (string) $ts['letter_spacing'] ) { $row['letter_spacing'] = round( (float) $ts['letter_spacing'], 3 ) . 'px'; }
				if ( isset( $ts['transform'] ) && '' !== $ts['transform'] ) { $row['transform'] = (string) $ts['transform']; }
				if ( null === $found ) { $rows[] = $row; } else { $rows[ $found ] = $row; }
			}
			if ( $rows !== $r0 ) { unysonplus_preset_store_set( 'font_sizes', $rows ); $changed[] = 'font_sizes'; }
		}

		return $changed;
	}

	/**
	 * New colour list onto the stored rows. Matching is by slug (after applying $renames), so a row keeps
	 * its position and any extra keys; a colour the builder added is appended. Colours missing from $in are
	 * kept — deleting a Unyson+ preset from another builder would orphan every element that references it.
	 */
	private static function merge_colors( array $rows, array $in, array $renames ) {
		$by_slug = array();
		foreach ( $rows as $i => $r ) { if ( is_array( $r ) && ! empty( $r['name'] ) ) { $by_slug[ self::slug( $r['name'] ) ] = $i; } }
		foreach ( $renames as $old => $new_name ) {
			if ( isset( $by_slug[ $old ] ) ) {
				$i = $by_slug[ $old ];
				$rows[ $i ]['name'] = (string) $new_name;
				unset( $by_slug[ $old ] );
				$by_slug[ self::slug( $new_name ) ] = $i;
			}
		}
		foreach ( $in as $c ) {
			if ( ! is_array( $c ) || empty( $c['color'] ) ) { continue; }
			$slug = ! empty( $c['slug'] ) ? (string) $c['slug'] : self::slug( $c['name'] ?? '' );
			if ( '' === $slug ) { continue; }
			if ( isset( $by_slug[ $slug ] ) ) {
				$i = $by_slug[ $slug ];
				if ( self::color( $rows[ $i ]['color'] ?? '' ) !== self::color( $c['color'] ) ) { $rows[ $i ]['color'] = self::color( $c['color'] ); }
			} else {
				$rows[] = array( 'name' => (string) ( $c['name'] ?? $slug ), 'color' => self::color( $c['color'] ) );
				$by_slug[ $slug ] = count( $rows ) - 1;
			}
		}
		return array_values( $rows );
	}

	private static function typo_from_style( array $row, array $st ) {
		if ( ! empty( $st['family'] ) ) { $row['family'] = (string) $st['family']; }
		if ( ! empty( $st['weight'] ) ) { $row['variation'] = ( '400' === (string) $st['weight'] ) ? 'regular' : (string) $st['weight']; }
		if ( ! empty( $st['size'] ) ) { $row['size'] = array( 'value' => (string) round( (float) $st['size'], 2 ), 'unit' => 'px' ); }
		if ( ! empty( $st['line_height'] ) ) { $row['line-height'] = (string) $st['line_height']; }
		if ( isset( $st['letter_spacing'] ) && '' !== (string) $st['letter_spacing'] ) { $row['letter-spacing'] = (string) round( (float) $st['letter_spacing'], 3 ); }
		if ( ! empty( $st['transform'] ) ) { $row['text-transform'] = (string) $st['transform']; }
		if ( isset( $st['color'] ) && '' !== $st['color'] ) { $row['color'] = (string) $st['color']; }
		return $row;
	}

	/* ------------------------------------------------------------------ *
	 * Storage + value helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Raw Theme Settings read — straight from the option row, like the preset store does, so a sync that
	 * runs inside a save hook never re-enters the option-type schema.
	 */
	public static function get( $key, $default = null ) {
		if ( ! class_exists( 'FW_WP_Option' ) || ! function_exists( 'fw' ) ) { return $default; }
		$v = FW_WP_Option::get( 'fw_theme_settings_options:' . fw()->theme->manifest->get_id(), $key, '__upw_unset__' );
		return '__upw_unset__' === $v ? $default : $v;
	}

	public static function set( $key, $value ) {
		if ( function_exists( 'fw_set_db_settings_option' ) ) { fw_set_db_settings_option( $key, $value ); }
	}

	/** Same derivation as unysonplus_color_preset_slug_map(). */
	public static function slug( $name ) {
		return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $name ) ), '-' );
	}

	/** Any stored colour shape → '#rrggbb' / 'rgba(…)' / ''. */
	public static function color( $v ) {
		if ( is_array( $v ) ) {
			$v = function_exists( 'unysonplus_resolve_preset_color' ) ? unysonplus_resolve_preset_color( $v ) : (string) ( $v['custom'] ?? '' );
		}
		$v = trim( (string) $v );
		if ( '' === $v ) { return ''; }
		if ( '#' !== $v[0] && ! preg_match( '/^(rgb|hsl)/i', $v ) && function_exists( 'unysonplus_resolve_preset_color' ) ) {
			$r = unysonplus_resolve_preset_color( $v );
			if ( '' !== $r ) { $v = $r; }
		}
		return class_exists( 'FW_SC_Style' ) ? FW_SC_Style::color( $v ) : strtolower( $v );
	}

	/** A plain colour → the compact `{ predefined, custom }` shape, bound to a palette slug when it matches one. */
	public static function compact( $hex ) {
		$hex = self::color( $hex );
		$map = function_exists( 'unysonplus_color_preset_slug_map' ) ? unysonplus_color_preset_slug_map() : array();
		foreach ( $map as $slug => $c ) {
			if ( self::color( $c ) === $hex ) { return array( 'predefined' => $slug, 'custom' => '' ); }
		}
		return array( 'predefined' => '', 'custom' => $hex );
	}

	private static function to_px( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) { return 0.0; }
		if ( is_numeric( $v ) ) { return (float) $v; }
		if ( preg_match( '/^(-?[\d.]+)px$/i', $v, $m ) ) { return (float) $m[1]; }
		if ( preg_match( '/^(-?[\d.]+)r?em$/i', $v, $m ) ) { return (float) $m[1] * 16; }
		return 0.0;
	}

	private static function unitless( $v ) {
		$v = trim( (string) $v );
		return ( '' !== $v && is_numeric( $v ) ) ? (float) $v : 0.0;
	}

	/** Letter spacing → px: "0.27px" → 0.27; "0.02em" and a legacy bare number (read as em) → em × size. */
	private static function ls_px( $v, $size_px ) {
		$v = trim( (string) $v );
		if ( '' === $v ) { return ''; }
		if ( preg_match( '/^(-?[\d.]+)em$/i', $v, $m ) || ( is_numeric( $v ) && ( $m = array( 1 => $v ) ) ) ) {
			return $size_px ? round( (float) $m[1] * $size_px, 3 ) : '';
		}
		return self::to_px( $v );
	}
}
