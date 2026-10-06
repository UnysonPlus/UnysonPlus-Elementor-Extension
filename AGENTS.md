# Elementor Widgets extension — agent notes

Unyson+ elements as native Elementor widgets (`up-<tag>`), edited in Elementor's side panel and
rendered by the shortcode's own view. The full reference — widget list, the option → control map,
the residue, the form and table codecs, the gotchas — is the AI Dev Kit doc
`docs/extensions/elementor.md`. Read it before changing anything here.

Layout:

- `class-fw-extension-elementor.php` — registers the widgets and the panel categories, loads assets
  (head pass, editor preview), hides Elementor's locked Pro tiles (setting `hide_pro_promos`).
- `lib/class-fw-elementor-option-bridge.php` — option schema ↔ Elementor controls, settings ↔ atts.
  **Not `includes/`**: the framework auto-includes that folder at load, before Elementor exists.
- `lib/class-fw-elementor-shortcode-widget.php` — the widget base class.
- `widgets/<tag>.php` — one declaration per element (+ a row in `get_widget_files()`).
- `static/js/widgets.js` — re-runs `window.fwShortcodeInit` on editor re-renders.
- `tests/settings-round-trip-test.php` — run after any change:
  `php wp-cli.phar --path=<install with Elementor> eval-file tests/settings-round-trip-test.php`

Widget names (`up-<tag>`) are a stable contract: the Site Converter writes them into saved documents.
