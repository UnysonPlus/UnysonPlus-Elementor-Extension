/* Unyson+ Elementor widgets — (re)initialise an element's scripts when Elementor reports it
   ready. The shortcode scripts initialise once, on page load; in the editor a widget is
   re-rendered as its settings change and the new markup needs initialising again. They
   expose that through the shared `window.fwShortcodeInit` registry (also used by the block
   editor): every entry is an idempotent init( scope ). On the front end this runs too,
   harmlessly — each init skips what it has already initialised. */
( function ( $ ) {
	'use strict';

	/**
	 * The editor builds a re-rendered widget's nodes in its OWN window and inserts them into
	 * the preview frame. They work as DOM, but `node instanceof HTMLElement` is false against
	 * this frame's constructors — and libraries check exactly that (Splide then reports "A
	 * track/list element is missing" for markup that is all there). Swapping each such node
	 * for a copy imported into this document gives them nodes they recognise. The foreign
	 * nodes can sit below wrappers the frame created itself, so the walk descends through
	 * local nodes until it meets them.
	 */
	function localise( node ) {
		[].slice.call( node.children ).forEach( function ( child ) {
			if ( child instanceof window.Element ) {
				localise( child );
			} else {
				node.replaceChild( document.importNode( child, true ), child );
			}
		} );
	}

	function ready( $scope ) {
		var root = $scope && $scope[ 0 ];

		if ( ! root || ! /(^|\s)elementor-widget-up-/.test( root.className ) ) {
			return;
		}

		localise( root );

		( window.fwShortcodeInit || [] ).forEach( function ( init ) {
			try {
				init( root );
			} catch ( e ) { /* one element's runtime must not break the others */ }
		} );
	}

	var registered = false;

	function register() {
		if ( registered || ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		registered = true;

		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', ready );

		// Elements that were already ready before this ran.
		$( '[class*="elementor-widget-up-"]' ).each( function () {
			ready( $( this ) );
		} );
	}

	// When an asset optimizer bundles this file, it can run after Elementor has already
	// fired its init event — so register now if Elementor is up, and on the event if not.
	register();
	$( window ).on( 'elementor/frontend/init', register );
} )( jQuery );
