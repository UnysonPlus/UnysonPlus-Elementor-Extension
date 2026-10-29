<?php
/**
 * A push REPLACES a heading's typography group; it never merges over what an earlier push left.
 *
 * A kit that once received an uppercase 112px h1 kept `h1_typography_text_transform: uppercase` after the
 * next site's push, because that site's h1 set no transform and only set fields were written. Every heading
 * on the new site then rendered in capitals. A Theme Settings heading style is the whole truth for that
 * heading: fields it leaves unset are cleared, and so is a colour it no longer has.
 *
 * Restores the kit's settings afterwards.
 *
 * Run:
 *   php D:/xampp/wp-cli.phar --path=D:/xampp/htdocs/elementor eval-file <this file>
 */

if ( ! class_exists( 'FW_Builder_Sync_Elementor' ) ) {
	fwrite( STDERR, "FAIL: design sync not loaded (activate the Elementor extension on an install with Elementor)\n" );
	exit( 1 );
}

$p = new FW_Builder_Sync_Elementor();
if ( ! $p->is_available() ) { fwrite( STDERR, "FAIL: Elementor not available\n" ); exit( 1 ); }

$kit  = (int) get_option( 'elementor_active_kit' );
$orig = get_post_meta( $kit, '_elementor_page_settings', true );
$pass = 0; $fail = 0;
$ok = function ( $label, $cond, $got = null ) use ( &$pass, &$fail ) {
	if ( $cond ) { $pass++; echo "  PASS  $label\n"; } else { $fail++; echo "  FAIL  $label" . ( null !== $got ? '  (got: ' . wp_json_encode( $got ) . ')' : '' ) . "\n"; }
};

try {
	// An earlier site's push: uppercase, a colour.
	$p->push( array( 'headings' => array( 'h1' => array( 'family' => 'Inter', 'size' => 112, 'transform' => 'uppercase', 'color' => '#ff0000' ) ) ) );
	$s = get_post_meta( $kit, '_elementor_page_settings', true );
	$ok( 'seed: the first push wrote uppercase + colour', 'uppercase' === ( $s['h1_typography_text_transform'] ?? '' ) && '#ff0000' === ( $s['h1_color'] ?? '' ), array( $s['h1_typography_text_transform'] ?? null, $s['h1_color'] ?? null ) );

	// The next site's push: no transform, no colour.
	$p->push( array( 'headings' => array( 'h1' => array( 'family' => 'Inter', 'size' => 64 ) ) ) );
	$s = get_post_meta( $kit, '_elementor_page_settings', true );
	$ok( 'push: the new size is written', 64.0 === (float) ( $s['h1_typography_font_size']['size'] ?? 0 ), $s['h1_typography_font_size'] ?? null );
	$ok( 'push: an unset transform clears the old uppercase', '' === (string) ( $s['h1_typography_text_transform'] ?? '' ), $s['h1_typography_text_transform'] ?? null );
	$ok( 'push: an unset colour clears the old colour', '' === (string) ( $s['h1_color'] ?? '' ), $s['h1_color'] ?? null );
	// A heading the NEW design does not define at all is reset too (an earlier site's uppercase h4 survived a push
	// whose Theme Settings had no h4, and capitalised the footer's link titles until they overflowed a phone).
	$p->push( array( 'headings' => array( 'h4' => array( 'family' => 'Inter', 'transform' => 'uppercase' ) ) ) );
	$p->push( array( 'headings' => array( 'h1' => array( 'family' => 'Inter', 'size' => 64 ) ) ) );
	$s = get_post_meta( $kit, '_elementor_page_settings', true );
	$ok( 'push: a heading the design leaves out is reset', '' === (string) ( $s['h4_typography_text_transform'] ?? '' ), $s['h4_typography_text_transform'] ?? null );
	// Layout lives in Theme Settings' General → Layout group (`general_layout`), where the theme reads it.
	$cw = fw_get_db_settings_option( 'general_layout/layout_container_width', array() );
	$d  = FW_Builder_Sync_Design::read();
	$ok( 'read: the container width comes from general_layout', isset( $cw['lg']['value'] ) && (float) $cw['lg']['value'] === (float) ( $d['container']['lg'] ?? 0 ), array( $cw['lg'] ?? null, $d['container'] ?? null ) );
} finally {
	update_post_meta( $kit, '_elementor_page_settings', $orig );
	if ( class_exists( '\Elementor\Plugin' ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
}

echo "\n$pass passed, $fail failed\n";
exit( $fail ? 1 : 0 );
