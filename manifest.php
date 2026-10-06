<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = [];

$manifest['name']        = __( 'Elementor Widgets', 'fw' );
$manifest['slug']        = 'unysonplus-elementor';
$manifest['description'] = __(
	'Exposes Unyson+ elements as native Elementor widgets, edited in Elementor\'s own side panel. Widgets are rendered by the same code as the page builder, so the front-end output is identical.',
	'fw'
);

$manifest['version']     = '1.0.5';
$manifest['display']     = true;
$manifest['standalone']  = true;

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-Elementor-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-Elementor-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '6.1';

/**
 * Requires the Shortcodes extension: every widget delegates its render to the
 * matching shortcode, so there is nothing to render without it.
 */
$manifest['requirements'] = array(
	'extensions' => array(
		'shortcodes' => array(),
	),
);

/**
 * Changelog
 * -----------------------------------------------------------------------------
 * 1.0.4 - WooCommerce widgets, in their own "Unyson+ Shop" panel category (only
 *         when the WooCommerce extension is active): Products, Product Card,
 *         Product Categories, Add to Cart, Menu Cart, Cart Link, Account Link,
 *         Product Search, Product Filters, Upsells, Wishlist, Compare, Product
 *         Page, Cart, Checkout, My Account, Order Tracking and Free Shipping Bar —
 *         53 widgets in all. Elements with no options of their own get a panel
 *         note instead of an empty panel; product widgets start on the first
 *         product.
 *
 * 1.0.3 - Replacements for Elementor's paid widgets: Posts, Flip Box, Call to Action,
 *         Countdown, Share Buttons, Animated Headline, Blockquote, Slides, Hotspot,
 *         Lottie, Table of Contents, Video Popup, Modal Popup, Team Member,
 *         Comparison Table, Before / After, Star Rating, Image Box and Form — 35
 *         widgets in all. The Form edits its fields in the panel (type, label,
 *         required, placeholder, choices, width) and submits through the forms
 *         extension under a stable form id. New setting, Unyson+ → Elementor
 *         Widgets: "Hide locked Pro widgets" (on by default, inert when Elementor
 *         Pro is active) removes Elementor's locked Pro tiles, its Atomic Form
 *         promotions and the upgrade banner from the widget panel. The bridge
 *         now also maps radio, checkboxes, date-time and single-option popovers.
 *
 * 1.0.2 - Eight more widgets: Gallery, Tabs, Timeline, Progress, Table, Tag List,
 *         Badge and Feature List. The option bridge now maps image galleries (to
 *         Elementor's Gallery control), table / image style presets, and the table
 *         editor's data — a repeater of rows with one cell per line, the column list
 *         following the widest row, and anything a line cannot hold (pricing cells,
 *         merged cells) kept in the row so it still renders exactly.
 *
 * 1.0.1 - Seven more widgets: Steps, Accordion, Pricing Table, Logo Grid, Counter,
 *         Icon Box and Newsletter. The option bridge now also maps icons (to Elementor's Icons
 *         control), multi-inline groups (one control per part), unit inputs (a
 *         slider with units) and button presets. Widgets fire the shortcode's
 *         per-instance asset action, so atts-dependent assets (the logo carousel's
 *         slider) load as on a page-builder page. Editor re-renders re-run every
 *         element's script through the shared window.fwShortcodeInit registry
 *         instead of per-widget handlers.
 *
 * 1.0.0 - Initial release. Unyson+ elements as native Elementor widgets in their
 *         own "Unyson+" panel category. A widget is a thin declaration (which
 *         shortcode, which of its options, grouped into which panel sections);
 *         an option bridge turns those Unyson+ options into Elementor controls
 *         and the saved control values back into shortcode atts, so the shortcode's
 *         own view renders the widget. First widget: Testimonials.
 */
