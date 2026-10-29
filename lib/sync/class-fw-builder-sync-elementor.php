<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Elementor side of the sync — the active Site Kit's global settings.
 *
 *   Unyson+ design                Elementor kit (`_elementor_page_settings` of the active kit)
 *   colors primary/secondary/accent  system_colors primary / secondary / accent
 *   text_color                     system_colors text  +  body_color
 *   other colors                   custom_colors (ids kept in the identity map, so a rename stays attached)
 *   fonts.heading                  system_typography primary (family)
 *   fonts.body                     system_typography text  +  body_typography_*
 *   headings h1–h6                 h1_typography_* … h6_typography_*, h1_color …
 *   link / link_hover              link_normal_color / link_hover_color
 *   container lg / md              container_width / container_width_tablet
 *   background                     body_background_background = classic, body_background_color
 *   button                         button_text_color, button_background_*, button_border_*, button_hover_*
 *   text_styles                    custom_typography
 *
 * The kit is written as one meta update followed by Elementor's own cache clear — the same two things
 * Kit::save() does — rather than through Document::update_settings(), whose array_replace_recursive()
 * merges lists by index and would leave a deleted colour behind.
 */
class FW_Builder_Sync_Elementor extends FW_Builder_Sync_Provider {

	const META = '_elementor_page_settings';

	/** Unyson+ palette slugs that are Elementor system colours. */
	private static $system = array( 'primary', 'secondary', 'accent' );

	public function slug() { return 'elementor'; }

	public function label() { return 'Elementor'; }

	public function is_available() {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->kits_manager );
	}

	public function listen( callable $on_saved ) {
		add_action( 'elementor/document/after_save', function ( $document ) use ( $on_saved ) {
			if ( $document instanceof \Elementor\Core\Kits\Documents\Kit
				&& (int) $document->get_main_id() === $this->kit_id() ) {
				call_user_func( $on_saved );
			}
		}, 20 );
	}

	/* ------------------------------------------------------------------ *
	 * Kit I/O
	 * ------------------------------------------------------------------ */

	private function kit_id() {
		return (int) get_option( 'elementor_active_kit' );
	}

	private function read_kit() {
		$id = $this->kit_id();
		if ( ! $id ) { return array(); }
		$s = get_post_meta( $id, self::META, true );
		return is_array( $s ) ? $s : array();
	}

	private function write_kit( array $s ) {
		$id = $this->kit_id();
		if ( ! $id ) { return; }
		update_post_meta( $id, self::META, wp_slash( $s ) );
		if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/* ------------------------------------------------------------------ *
	 * Push: design → kit
	 * ------------------------------------------------------------------ */

	public function push( array $d ) {
		if ( ! $this->is_available() || ! $this->kit_id() ) { return array(); }
		$s   = $this->read_kit();
		$s0  = $s;
		$ids = $this->ids();

		// Colours.
		if ( ! empty( $d['colors'] ) ) {
			$sys = $this->index( $s['system_colors'] ?? $this->default_system_colors(), '_id' );
			$custom = isset( $s['custom_colors'] ) && is_array( $s['custom_colors'] ) ? $s['custom_colors'] : array();
			$managed = array_flip( array_filter( (array) ( $ids['colors'] ?? array() ) ) ); // kit id => slug
			$keep = array();
			foreach ( $custom as $c ) {
				if ( is_array( $c ) && isset( $c['_id'] ) && ! isset( $managed[ $c['_id'] ] ) ) { $keep[] = $c; } // a user's own Elementor colour
			}
			$new_custom = array();
			$new_ids    = array();
			foreach ( $d['colors'] as $c ) {
				if ( in_array( $c['slug'], self::$system, true ) ) {
					$sys[ $c['slug'] ] = array( '_id' => $c['slug'], 'title' => $c['name'], 'color' => $c['color'] );
					continue;
				}
				$kid = $ids['colors'][ $c['slug'] ] ?? self::make_id( 'c', $c['slug'] );
				$new_ids[ $c['slug'] ] = $kid;
				$new_custom[] = array( '_id' => $kid, 'title' => $c['name'], 'color' => $c['color'] );
			}
			if ( ! empty( $d['text_color'] ) ) {
				$sys['text'] = array( '_id' => 'text', 'title' => $sys['text']['title'] ?? 'Text', 'color' => $d['text_color'] );
			}
			$s['system_colors'] = array_values( $sys );
			$s['custom_colors'] = array_merge( $new_custom, $keep );
			$ids['colors']      = $new_ids;
		}
		if ( ! empty( $d['text_color'] ) ) { $s['body_color'] = $d['text_color']; }
		if ( ! empty( $d['link'] ) ) { $s['link_normal_color'] = $d['link']; }
		if ( ! empty( $d['link_hover'] ) ) { $s['link_hover_color'] = $d['link_hover']; }

		// Fonts.
		$st = $this->index( $s['system_typography'] ?? $this->default_system_typography(), '_id' );
		if ( ! empty( $d['fonts']['heading']['family'] ) ) {
			$st['primary'] = array_merge( (array) ( $st['primary'] ?? array( '_id' => 'primary', 'title' => 'Primary' ) ),
				array( 'typography_typography' => 'custom', 'typography_font_family' => $d['fonts']['heading']['family'] ) );
		}
		if ( ! empty( $d['fonts']['body'] ) ) {
			$st['text'] = array_merge( (array) ( $st['text'] ?? array( '_id' => 'text', 'title' => 'Text' ) ),
				$this->typo_settings( 'typography', $d['fonts']['body'] ) );
			$s = array_merge( $s, $this->typo_settings( 'body_typography', $d['fonts']['body'], true ) );
		}
		$s['system_typography'] = array_values( $st );
		// A heading style is the whole truth for that heading: the group is REPLACED, so a field this design
		// leaves unset (no transform, no colour) clears what an earlier push wrote instead of surviving it.
		// …and a heading level the design does not define at all is reset to Elementor's default, or an earlier
		// site's style for it (an uppercase h4) survives every later push. Only when the design carries headings.
		if ( isset( $d['headings'] ) ) {
			foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
				$style = (array) ( $d['headings'][ $h ] ?? array() );
				$t     = $this->typo_settings( $h . '_typography', $style, true );
				if ( ! $t ) {
					$t = array( $h . '_typography_typography' => '' ) + array_fill_keys( array( $h . '_typography_font_family', $h . '_typography_font_weight', $h . '_typography_text_transform' ), '' )
						+ array( $h . '_typography_font_size' => array( 'unit' => 'px', 'size' => '', 'sizes' => array() ), $h . '_typography_line_height' => array( 'unit' => 'em', 'size' => '', 'sizes' => array() ), $h . '_typography_letter_spacing' => array( 'unit' => 'px', 'size' => '', 'sizes' => array() ) );
				}
				$s = array_merge( $s, $t );
				$s[ $h . '_color' ] = (string) ( $style['color'] ?? '' );
			}
		}

		// Text Styles → custom typography (ours are tracked; a user's own entries are kept).
		if ( isset( $d['text_styles'] ) ) {
			$ct      = isset( $s['custom_typography'] ) && is_array( $s['custom_typography'] ) ? $s['custom_typography'] : array();
			$managed = array_flip( array_filter( (array) ( $ids['text_styles'] ?? array() ) ) );
			$keep    = array();
			foreach ( $ct as $c ) { if ( is_array( $c ) && isset( $c['_id'] ) && ! isset( $managed[ $c['_id'] ] ) ) { $keep[] = $c; } }
			$new = array();
			$nid = array();
			foreach ( (array) $d['text_styles'] as $ts ) {
				$slug = FW_Builder_Sync_Design::slug( $ts['name'] );
				$kid  = $ids['text_styles'][ $slug ] ?? self::make_id( 't', $slug );
				$nid[ $slug ] = $kid;
				$new[] = array_merge( array( '_id' => $kid, 'title' => $ts['name'] ), $this->typo_settings( 'typography', $ts ) );
			}
			$s['custom_typography'] = array_merge( $new, $keep );
			$ids['text_styles']     = $nid;
		}

		// Layout + background.
		if ( ! empty( $d['container']['lg'] ) ) { $s['container_width'] = array( 'unit' => 'px', 'size' => $d['container']['lg'], 'sizes' => array() ); }
		if ( ! empty( $d['container']['md'] ) ) { $s['container_width_tablet'] = array( 'unit' => 'px', 'size' => $d['container']['md'], 'sizes' => array() ); }
		if ( ! empty( $d['background'] ) ) {
			$s['body_background_background'] = 'classic';
			$s['body_background_color']      = $d['background'];
		}

		// The global button.
		$b = (array) ( $d['button'] ?? array() );
		if ( ! empty( $b['text'] ) ) { $s['button_text_color'] = $b['text']; }
		if ( ! empty( $b['bg'] ) ) { $s['button_background_background'] = 'classic'; $s['button_background_color'] = $b['bg']; }
		if ( ! empty( $b['border'] ) ) {
			$w = ! empty( $b['border_width'] ) ? (string) $b['border_width'] : '1';
			$s['button_border_border'] = 'solid';
			$s['button_border_color']  = $b['border'];
			$s['button_border_width']  = array( 'unit' => 'px', 'top' => $w, 'right' => $w, 'bottom' => $w, 'left' => $w, 'isLinked' => true );
		}
		if ( ! empty( $b['hover_text'] ) ) { $s['button_hover_text_color'] = $b['hover_text']; }
		if ( ! empty( $b['hover_bg'] ) ) { $s['button_hover_background_background'] = 'classic'; $s['button_hover_background_color'] = $b['hover_bg']; }
		if ( ! empty( $b['hover_border'] ) ) { $s['button_hover_border_border'] = 'solid'; $s['button_hover_border_color'] = $b['hover_border']; }

		$this->save_ids( $ids );
		if ( $s === $s0 ) { return array(); }
		$this->write_kit( $s );
		return array_keys( self::changed_keys( $s0, $s ) );
	}

	/* ------------------------------------------------------------------ *
	 * Pull: kit → design
	 * ------------------------------------------------------------------ */

	public function pull() {
		if ( ! $this->is_available() || ! $this->kit_id() ) { return array( 'design' => array(), 'renames' => array() ); }
		$s       = $this->read_kit();
		$ids     = $this->ids();
		$d       = array();
		$renames = array();

		// Colours.
		$colors = array();
		foreach ( (array) ( $s['system_colors'] ?? array() ) as $c ) {
			if ( ! is_array( $c ) || empty( $c['color'] ) ) { continue; }
			if ( in_array( $c['_id'] ?? '', self::$system, true ) ) {
				$colors[] = array( 'slug' => $c['_id'], 'name' => ucfirst( $c['_id'] ), 'color' => FW_Builder_Sync_Design::color( $c['color'] ) );
			} elseif ( 'text' === ( $c['_id'] ?? '' ) ) {
				$d['text_color'] = FW_Builder_Sync_Design::color( $c['color'] );
			}
		}
		$by_kid  = array_flip( array_filter( (array) ( $ids['colors'] ?? array() ) ) );
		$new_ids = (array) ( $ids['colors'] ?? array() );
		foreach ( (array) ( $s['custom_colors'] ?? array() ) as $c ) {
			if ( ! is_array( $c ) || empty( $c['color'] ) || empty( $c['_id'] ) ) { continue; }
			$title_slug = FW_Builder_Sync_Design::slug( $c['title'] ?? '' );
			if ( '' === $title_slug ) { continue; }
			if ( isset( $by_kid[ $c['_id'] ] ) && $by_kid[ $c['_id'] ] !== $title_slug ) {
				// Renamed in Elementor: rename the Unyson+ row in place, keep the id attached.
				$renames[ $by_kid[ $c['_id'] ] ] = (string) $c['title'];
				unset( $new_ids[ $by_kid[ $c['_id'] ] ] );
			}
			$new_ids[ $title_slug ] = $c['_id'];
			$colors[] = array( 'slug' => $title_slug, 'name' => (string) $c['title'], 'color' => FW_Builder_Sync_Design::color( $c['color'] ) );
		}
		if ( $colors ) { $d['colors'] = $colors; }
		$ids['colors'] = $new_ids;

		if ( ! empty( $s['body_color'] ) ) { $d['text_color'] = FW_Builder_Sync_Design::color( $s['body_color'] ); }
		if ( ! empty( $s['link_normal_color'] ) ) { $d['link'] = FW_Builder_Sync_Design::color( $s['link_normal_color'] ); }
		if ( ! empty( $s['link_hover_color'] ) ) { $d['link_hover'] = FW_Builder_Sync_Design::color( $s['link_hover_color'] ); }

		// Fonts.
		$st = $this->index( $s['system_typography'] ?? array(), '_id' );
		if ( ! empty( $st['primary']['typography_font_family'] ) ) { $d['fonts']['heading'] = array( 'family' => (string) $st['primary']['typography_font_family'] ); }
		$body = $this->style_from( $s, 'body_typography' );
		if ( ! array_filter( $body ) && isset( $st['text'] ) ) { $body = $this->style_from( $st['text'], 'typography' ); }
		if ( array_filter( $body ) ) { $d['fonts']['body'] = $body; }
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
			$style = $this->style_from( $s, $h . '_typography' );
			if ( ! empty( $s[ $h . '_color' ] ) ) { $style['color'] = FW_Builder_Sync_Design::color( $s[ $h . '_color' ] ); }
			if ( array_filter( $style ) ) { $d['headings'][ $h ] = $style; }
		}

		// Text Styles ← custom typography.
		$ts  = array();
		$tid = array();
		foreach ( (array) ( $s['custom_typography'] ?? array() ) as $c ) {
			if ( ! is_array( $c ) || empty( $c['title'] ) ) { continue; }
			$ts[] = array( 'name' => (string) $c['title'] ) + $this->style_from( $c, 'typography' );
			$tid[ FW_Builder_Sync_Design::slug( $c['title'] ) ] = (string) ( $c['_id'] ?? '' );
		}
		if ( $ts ) { $d['text_styles'] = $ts; $ids['text_styles'] = $tid; }

		// Layout + background.
		if ( ! empty( $s['container_width']['size'] ) && 'px' === ( $s['container_width']['unit'] ?? 'px' ) ) { $d['container']['lg'] = (float) $s['container_width']['size']; }
		if ( ! empty( $s['container_width_tablet']['size'] ) && 'px' === ( $s['container_width_tablet']['unit'] ?? 'px' ) ) { $d['container']['md'] = (float) $s['container_width_tablet']['size']; }
		if ( 'classic' === ( $s['body_background_background'] ?? '' ) && ! empty( $s['body_background_color'] ) ) { $d['background'] = FW_Builder_Sync_Design::color( $s['body_background_color'] ); }

		// Button.
		$b = array();
		if ( ! empty( $s['button_text_color'] ) ) { $b['text'] = FW_Builder_Sync_Design::color( $s['button_text_color'] ); }
		if ( ! empty( $s['button_background_color'] ) ) { $b['bg'] = FW_Builder_Sync_Design::color( $s['button_background_color'] ); }
		if ( ! empty( $s['button_border_color'] ) ) { $b['border'] = FW_Builder_Sync_Design::color( $s['button_border_color'] ); }
		if ( ! empty( $s['button_border_width']['top'] ) ) { $b['border_width'] = (float) $s['button_border_width']['top']; }
		if ( ! empty( $s['button_hover_text_color'] ) ) { $b['hover_text'] = FW_Builder_Sync_Design::color( $s['button_hover_text_color'] ); }
		if ( ! empty( $s['button_hover_background_color'] ) ) { $b['hover_bg'] = FW_Builder_Sync_Design::color( $s['button_hover_background_color'] ); }
		if ( ! empty( $s['button_hover_border_color'] ) ) { $b['hover_border'] = FW_Builder_Sync_Design::color( $s['button_hover_border_color'] ); }
		if ( $b ) { $d['button'] = $b; }

		$this->save_ids( $ids );
		return array( 'design' => $d, 'renames' => $renames );
	}

	/* ------------------------------------------------------------------ *
	 * Helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Style → Elementor typography group settings under $prefix (e.g. "h1_typography"). With $replace, every
	 * field of the group is written, an unset one as empty (Elementor's "default"), so the group replaces
	 * whatever the kit held rather than merging over it.
	 */
	private function typo_settings( $prefix, array $st, $replace = false ) {
		$out = array( $prefix . '_typography' => 'custom' );
		if ( $replace ) {
			$empty = array( 'unit' => 'px', 'size' => '', 'sizes' => array() );
			$out  += array( $prefix . '_font_family' => '', $prefix . '_font_weight' => '', $prefix . '_text_transform' => '',
				$prefix . '_font_size' => $empty, $prefix . '_line_height' => array( 'unit' => 'em', 'size' => '', 'sizes' => array() ), $prefix . '_letter_spacing' => $empty );
		}
		$set = 0;
		if ( ! empty( $st['family'] ) ) { $out[ $prefix . '_font_family' ] = (string) $st['family']; $set++; }
		if ( ! empty( $st['weight'] ) ) { $out[ $prefix . '_font_weight' ] = (string) $st['weight']; $set++; }
		if ( ! empty( $st['size'] ) ) { $out[ $prefix . '_font_size' ] = array( 'unit' => 'px', 'size' => round( (float) $st['size'], 2 ), 'sizes' => array() ); $set++; }
		if ( ! empty( $st['line_height'] ) ) { $out[ $prefix . '_line_height' ] = array( 'unit' => 'em', 'size' => round( (float) $st['line_height'], 3 ), 'sizes' => array() ); $set++; }
		if ( isset( $st['letter_spacing'] ) && '' !== (string) $st['letter_spacing'] ) { $out[ $prefix . '_letter_spacing' ] = array( 'unit' => 'px', 'size' => round( (float) $st['letter_spacing'], 3 ), 'sizes' => array() ); $set++; }
		if ( ! empty( $st['transform'] ) ) { $out[ $prefix . '_text_transform' ] = (string) $st['transform']; $set++; }
		return $set ? $out : array();
	}

	/** Elementor typography group under $prefix → Style. */
	private function style_from( array $s, $prefix ) {
		$size = $s[ $prefix . '_font_size' ] ?? array();
		$lh   = $s[ $prefix . '_line_height' ] ?? array();
		$ls   = $s[ $prefix . '_letter_spacing' ] ?? array();
		$lhv  = 0.0;
		if ( ! empty( $lh['size'] ) ) {
			$lhv = (float) $lh['size'];
			if ( 'px' === ( $lh['unit'] ?? 'em' ) && ! empty( $size['size'] ) ) { $lhv = round( $lhv / (float) $size['size'], 3 ); }
		}
		return array_filter( array(
			'family'         => (string) ( $s[ $prefix . '_font_family' ] ?? '' ),
			'weight'         => (string) ( $s[ $prefix . '_font_weight' ] ?? '' ),
			'size'           => ( ! empty( $size['size'] ) && 'px' === ( $size['unit'] ?? 'px' ) ) ? (float) $size['size'] : 0.0,
			'line_height'    => $lhv,
			'letter_spacing' => ( isset( $ls['size'] ) && '' !== (string) $ls['size'] && 'px' === ( $ls['unit'] ?? 'px' ) ) ? (float) $ls['size'] : '',
			'transform'      => (string) ( $s[ $prefix . '_text_transform' ] ?? '' ),
		), function ( $v ) { return '' !== $v && 0.0 !== $v; } );
	}

	private function index( $list, $key ) {
		$out = array();
		foreach ( (array) $list as $row ) {
			if ( is_array( $row ) && isset( $row[ $key ] ) ) { $out[ (string) $row[ $key ] ] = $row; }
		}
		return $out;
	}

	private function default_system_colors() {
		return array(
			array( '_id' => 'primary', 'title' => 'Primary', 'color' => '#6EC1E4' ),
			array( '_id' => 'secondary', 'title' => 'Secondary', 'color' => '#54595F' ),
			array( '_id' => 'text', 'title' => 'Text', 'color' => '#7A7A7A' ),
			array( '_id' => 'accent', 'title' => 'Accent', 'color' => '#61CE70' ),
		);
	}

	private function default_system_typography() {
		return array(
			array( '_id' => 'primary', 'title' => 'Primary' ),
			array( '_id' => 'secondary', 'title' => 'Secondary' ),
			array( '_id' => 'text', 'title' => 'Text' ),
			array( '_id' => 'accent', 'title' => 'Accent' ),
		);
	}

	/** A stable 7-character id per kind + slug (Elementor's own ids are 7-char hex). */
	private static function make_id( $kind, $slug ) {
		return substr( md5( 'upw-' . $kind . '-' . $slug ), 0, 7 );
	}

	private static function changed_keys( array $a, array $b ) {
		$keys = array();
		foreach ( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) as $k ) {
			if ( ( $a[ $k ] ?? null ) !== ( $b[ $k ] ?? null ) ) { $keys[ $k ] = true; }
		}
		return $keys;
	}
}
