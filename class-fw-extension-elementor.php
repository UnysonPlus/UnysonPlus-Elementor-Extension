<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Unyson+ elements as Elementor widgets.
 *
 * Inert unless Elementor is active. A widget is one file under widgets/ declaring a
 * class FW_Elementor_Widget_<Name> (see FW_Elementor_Shortcode_Widget) — adding an
 * element is adding a file and a line to get_widget_files().
 */
class FW_Extension_Elementor extends FW_Extension {

	/**
	 * @internal
	 */
	public function _init() {
		// No Elementor dependency at load: settings_from_atts() must be callable from a
		// CLI / cron import that never loads the editor.
		require_once $this->get_path( '/lib/class-fw-elementor-option-bridge.php' );

		add_action( 'elementor/elements/categories_registered', array( $this, '_action_register_category' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, '_action_order_categories' ), 999 );
		add_action( 'elementor/widgets/register', array( $this, '_action_register_widgets' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, '_action_register_scripts' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, '_action_preview_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, '_action_document_assets' ), 20 );
		add_filter( 'fw_is_editor_context', array( $this, '_filter_editor_context' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, '_action_editor_icons' ) );

		if ( defined( 'ELEMENTOR_VERSION' ) && ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_filter( 'transient_elementor_remote_info_api_data_' . ELEMENTOR_VERSION, array( $this, '_filter_hide_pro_promos' ) );
			add_action( 'elementor/editor/after_enqueue_styles', array( $this, '_action_hide_pro_promo_tiles' ) );
		}
	}

	/**
	 * The locked tiles that do not come from `pro_widgets`: Elementor 4's Atomic Form category
	 * (built inside its elements manager, every item a Pro promotion, no hook to remove it),
	 * any other locked tile, and the "Access all Pro widgets" banners (under the widget list and
	 * under a widget's settings). Hidden in the
	 * panel only — nothing is unregistered.
	 *
	 * @internal
	 */
	public function _action_hide_pro_promo_tiles() {
		if ( 'no' === fw_get_db_ext_settings_option( $this->get_name(), 'hide_pro_promos', 'yes' ) ) {
			return;
		}

		wp_register_style( 'fw-elementor-hide-promos', false, array(), $this->manifest->get_version() );
		wp_enqueue_style( 'fw-elementor-hide-promos' );
		wp_add_inline_style( 'fw-elementor-hide-promos', '#elementor-panel .elementor-element--promotion, #elementor-panel-category-atomic-form, #elementor-panel-get-pro-elements-sticky, #elementor-panel .elementor-panel-editor-sticky-promotion { display: none !important; }' );
	}

	/**
	 * Hide Elementor's locked Pro tiles from the widget panel.
	 *
	 * They come from Elementor's cached remote info data (`pro_widgets`), and the editor
	 * merges them back in AFTER its public `elementor/editor/localize_settings` filter for an
	 * admin without Pro — so removing them there is undone. Removing them where they are read
	 * from is what sticks. Only `pro_widgets` is removed; the rest of the data is untouched.
	 * Off when Elementor Pro is active (the filter is not added) or when the setting is off.
	 *
	 * @internal
	 */
	public function _filter_hide_pro_promos( $data ) {
		if ( ! is_array( $data ) || empty( $data['pro_widgets'] ) ) {
			return $data;
		}

		if ( 'no' === fw_get_db_ext_settings_option( $this->get_name(), 'hide_pro_promos', 'yes' ) ) {
			return $data;
		}

		$data['pro_widgets'] = array();

		return $data;
	}

	/**
	 * The widgets' panel icons: each element's own 16×16 pixel glyph, drawn as a CSS mask so it
	 * takes the panel's text colour (and its hover / dark-mode colour) like Elementor's own icon
	 * font does. Editor only.
	 *
	 * @internal
	 */
	public function _action_editor_icons() {
		$css = '.up-icon{display:inline-block;width:1em;height:1em;background-color:currentColor;'
			. '-webkit-mask:var(--up-icon) center/contain no-repeat;mask:var(--up-icon) center/contain no-repeat;}'
			. '.up-icon::before{content:none;}';

		foreach ( $this->get_widgets() as $name => $widget ) {
			$uri = $widget->icon_uri();
			if ( '' !== $uri ) {
				$css .= '.up-icon--' . sanitize_html_class( $name ) . '{--up-icon:url("' . esc_url_raw( $uri ) . '");}';
			}
		}

		wp_register_style( 'fw-elementor-icons', false, array(), $this->manifest->get_version() );
		wp_enqueue_style( 'fw-elementor-icons' );
		wp_add_inline_style( 'fw-elementor-icons', $css );
	}

	/**
	 * Elementor's preview frame is a front-end request, so the framework would not know an
	 * author is looking at it — an element with nothing to show (a video popup without a
	 * video yet) would render nothing and Elementor would drop it. Its empty-state hint
	 * shows instead.
	 *
	 * @internal
	 */
	public function _filter_editor_context( $is_editor ) {
		if ( $is_editor || ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->preview ) ) {
			return $is_editor;
		}

		return \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/**
	 * The widgets this extension provides: shortcode tag => [ file (under widgets/), class ].
	 *
	 * @return array
	 */
	public function get_widget_files() {
		return apply_filters( 'fw_ext_elementor_widgets', array(
			'testimonials'  => array( 'file' => 'testimonials.php', 'class' => 'FW_Elementor_Widget_Testimonials' ),
			'steps'         => array( 'file' => 'steps.php', 'class' => 'FW_Elementor_Widget_Steps' ),
			'accordion'     => array( 'file' => 'accordion.php', 'class' => 'FW_Elementor_Widget_Accordion' ),
			'pricing_table' => array( 'file' => 'pricing-table.php', 'class' => 'FW_Elementor_Widget_Pricing_Table' ),
			'logo_grid'     => array( 'file' => 'logo-grid.php', 'class' => 'FW_Elementor_Widget_Logo_Grid' ),
			'counter'       => array( 'file' => 'counter.php', 'class' => 'FW_Elementor_Widget_Counter' ),
			'icon_box'      => array( 'file' => 'icon-box.php', 'class' => 'FW_Elementor_Widget_Icon_Box' ),
			'newsletter'    => array( 'file' => 'newsletter.php', 'class' => 'FW_Elementor_Widget_Newsletter' ),
			'gallery'       => array( 'file' => 'gallery.php', 'class' => 'FW_Elementor_Widget_Gallery' ),
			'tabs'          => array( 'file' => 'tabs.php', 'class' => 'FW_Elementor_Widget_Tabs' ),
			'timeline'      => array( 'file' => 'timeline.php', 'class' => 'FW_Elementor_Widget_Timeline' ),
			'progress'      => array( 'file' => 'progress.php', 'class' => 'FW_Elementor_Widget_Progress' ),
			'table'         => array( 'file' => 'table.php', 'class' => 'FW_Elementor_Widget_Table' ),
			'tag_list'      => array( 'file' => 'tag-list.php', 'class' => 'FW_Elementor_Widget_Tag_List' ),
			'badge'         => array( 'file' => 'badge.php', 'class' => 'FW_Elementor_Widget_Badge' ),
			'feature_list'  => array( 'file' => 'feature-list.php', 'class' => 'FW_Elementor_Widget_Feature_List' ),
			'posts'             => array( 'file' => 'posts.php', 'class' => 'FW_Elementor_Widget_Posts' ),
			'flip_box'          => array( 'file' => 'flip-box.php', 'class' => 'FW_Elementor_Widget_Flip_Box' ),
			'call_to_action'    => array( 'file' => 'call-to-action.php', 'class' => 'FW_Elementor_Widget_Call_To_Action' ),
			'countdown'         => array( 'file' => 'countdown.php', 'class' => 'FW_Elementor_Widget_Countdown' ),
			'social_share'      => array( 'file' => 'social-share.php', 'class' => 'FW_Elementor_Widget_Social_Share' ),
			'animated_heading'  => array( 'file' => 'animated-heading.php', 'class' => 'FW_Elementor_Widget_Animated_Heading' ),
			'blockquote'        => array( 'file' => 'blockquote.php', 'class' => 'FW_Elementor_Widget_Blockquote' ),
			'carousel'          => array( 'file' => 'carousel.php', 'class' => 'FW_Elementor_Widget_Carousel' ),
			'image_hotspots'    => array( 'file' => 'image-hotspots.php', 'class' => 'FW_Elementor_Widget_Image_Hotspots' ),
			'lottie'            => array( 'file' => 'lottie.php', 'class' => 'FW_Elementor_Widget_Lottie' ),
			'toc'               => array( 'file' => 'toc.php', 'class' => 'FW_Elementor_Widget_Toc' ),
			'video_popup'       => array( 'file' => 'video-popup.php', 'class' => 'FW_Elementor_Widget_Video_Popup' ),
			'modal_popup'       => array( 'file' => 'modal-popup.php', 'class' => 'FW_Elementor_Widget_Modal_Popup' ),
			'team_member'       => array( 'file' => 'team-member.php', 'class' => 'FW_Elementor_Widget_Team_Member' ),
			'comparison_table'  => array( 'file' => 'comparison-table.php', 'class' => 'FW_Elementor_Widget_Comparison_Table' ),
			'before_after'      => array( 'file' => 'before-after.php', 'class' => 'FW_Elementor_Widget_Before_After' ),
			'star_rating'       => array( 'file' => 'star-rating.php', 'class' => 'FW_Elementor_Widget_Star_Rating' ),
			'image_box'         => array( 'file' => 'image-box.php', 'class' => 'FW_Elementor_Widget_Image_Box' ),
			'contact_form'      => array( 'file' => 'contact-form.php', 'class' => 'FW_Elementor_Widget_Contact_Form' ),
			'wc_products'           => array( 'file' => 'wc-products.php', 'class' => 'FW_Elementor_Widget_Wc_Products' ),
			'wc_product'            => array( 'file' => 'wc-product.php', 'class' => 'FW_Elementor_Widget_Wc_Product' ),
			'wc_product_categories' => array( 'file' => 'wc-product-categories.php', 'class' => 'FW_Elementor_Widget_Wc_Product_Categories' ),
			'wc_add_to_cart'        => array( 'file' => 'wc-add-to-cart.php', 'class' => 'FW_Elementor_Widget_Wc_Add_To_Cart' ),
			'wc_mini_cart'          => array( 'file' => 'wc-mini-cart.php', 'class' => 'FW_Elementor_Widget_Wc_Mini_Cart' ),
			'wc_cart_link'          => array( 'file' => 'wc-cart-link.php', 'class' => 'FW_Elementor_Widget_Wc_Cart_Link' ),
			'wc_account'            => array( 'file' => 'wc-account.php', 'class' => 'FW_Elementor_Widget_Wc_Account' ),
			'wc_product_search'     => array( 'file' => 'wc-product-search.php', 'class' => 'FW_Elementor_Widget_Wc_Product_Search' ),
			'wc_product_filters'    => array( 'file' => 'wc-product-filters.php', 'class' => 'FW_Elementor_Widget_Wc_Product_Filters' ),
			'wc_upsells'            => array( 'file' => 'wc-upsells.php', 'class' => 'FW_Elementor_Widget_Wc_Upsells' ),
			'wc_wishlist'           => array( 'file' => 'wc-wishlist.php', 'class' => 'FW_Elementor_Widget_Wc_Wishlist' ),
			'wc_compare'            => array( 'file' => 'wc-compare.php', 'class' => 'FW_Elementor_Widget_Wc_Compare' ),
			'wc_product_page'       => array( 'file' => 'wc-product-page.php', 'class' => 'FW_Elementor_Widget_Wc_Product_Page' ),
			'wc_cart'               => array( 'file' => 'wc-cart.php', 'class' => 'FW_Elementor_Widget_Wc_Cart' ),
			'wc_checkout'           => array( 'file' => 'wc-checkout.php', 'class' => 'FW_Elementor_Widget_Wc_Checkout' ),
			'wc_my_account'         => array( 'file' => 'wc-my-account.php', 'class' => 'FW_Elementor_Widget_Wc_My_Account' ),
			'wc_order_tracking'     => array( 'file' => 'wc-order-tracking.php', 'class' => 'FW_Elementor_Widget_Wc_Order_Tracking' ),
			'wc_free_shipping'      => array( 'file' => 'wc-free-shipping.php', 'class' => 'FW_Elementor_Widget_Wc_Free_Shipping' ),
			'gallery_3d'                => array( 'file' => 'gallery-3d.php', 'class' => 'FW_Elementor_Widget_Gallery_3d' ),
			'image_scroll_choreography' => array( 'file' => 'image-scroll-choreography.php', 'class' => 'FW_Elementor_Widget_Image_Scroll_Choreography' ),
			'text_scroll_choreography'  => array( 'file' => 'text-scroll-choreography.php', 'class' => 'FW_Elementor_Widget_Text_Scroll_Choreography' ),
			'image_sequence'            => array( 'file' => 'image-sequence.php', 'class' => 'FW_Elementor_Widget_Image_Sequence' ),
			'interactive_reveal'        => array( 'file' => 'interactive-reveal.php', 'class' => 'FW_Elementor_Widget_Interactive_Reveal' ),
			'model_viewer'              => array( 'file' => 'model-viewer.php', 'class' => 'FW_Elementor_Widget_Model_Viewer' ),
			'parallax_scene'            => array( 'file' => 'parallax-scene.php', 'class' => 'FW_Elementor_Widget_Parallax_Scene' ),
			'rive'                      => array( 'file' => 'rive.php', 'class' => 'FW_Elementor_Widget_Rive' ),
			'svg_draw'                  => array( 'file' => 'svg-draw.php', 'class' => 'FW_Elementor_Widget_Svg_Draw' ),
			'svg_morph'                 => array( 'file' => 'svg-morph.php', 'class' => 'FW_Elementor_Widget_Svg_Morph' ),
			'webgl_object'              => array( 'file' => 'webgl-object.php', 'class' => 'FW_Elementor_Widget_Webgl_Object' ),
		) );
	}

	/**
	 * The Elementor widget name that renders a shortcode, or '' when there is none.
	 *
	 * Names are 'up-' + the tag with '_' -> '-' and are a STABLE contract (the Site
	 * Converter writes them into saved documents); renaming one is a breaking change.
	 * Answers without the editor loaded, so it is safe in CLI / cron imports.
	 *
	 * @param string $tag Shortcode tag.
	 * @return string
	 */
	public function widget_for_shortcode( $tag ) {
		$files = $this->get_widget_files();

		if ( ! isset( $files[ $tag ] ) || ! did_action( 'elementor/loaded' ) || ! is_readable( $this->get_path( '/widgets/' . $files[ $tag ]['file'] ) ) ) {
			return '';
		}

		$shortcodes = fw_ext( 'shortcodes' );

		return ( $shortcodes && $shortcodes->get_shortcode( $tag ) ) ? 'up-' . str_replace( '_', '-', $tag ) : '';
	}

	/** Load the widget base class and every widget class (needs Elementor's classes). */
	private function load_widget_classes() {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return false;
		}

		require_once $this->get_path( '/lib/class-fw-elementor-shortcode-widget.php' );

		foreach ( $this->get_widget_files() as $tag => $widget ) {
			$path = $this->get_path( '/widgets/' . $widget['file'] );

			if ( ! class_exists( $widget['class'] ) && is_readable( $path ) ) {
				require_once $path;
			}
		}

		return true;
	}

	/**
	 * The widget instance for a shortcode — the registered one when Elementor has
	 * registered its widgets, otherwise a standalone instance (enough for converting
	 * settings; controls are built on first use).
	 *
	 * @param string $tag Shortcode tag.
	 * @return FW_Elementor_Shortcode_Widget|null
	 */
	public function get_widget( $tag ) {
		static $standalone = array();

		$name = $this->widget_for_shortcode( $tag );

		if ( '' === $name || ! $this->load_widget_classes() ) {
			return null;
		}

		$files = $this->get_widget_files();

		if ( ! class_exists( $files[ $tag ]['class'] ) ) {
			return null;
		}

		if ( ! isset( $standalone[ $tag ] ) ) {
			$standalone[ $tag ] = new $files[ $tag ]['class']();
		}

		return $standalone[ $tag ];
	}

	/** @internal */
	public function _action_register_category( $elements_manager ) {
		$elements_manager->add_category( 'unysonplus', array(
			'title' => __( 'Unyson+', 'fw' ),
			'icon'  => 'eicon-apps',
		) );

		// The WooCommerce elements get their own category (Elementor's own WooCommerce one
		// is a locked Pro promotion). Registered only when they can render.
		if ( fw_ext( 'woocommerce' ) ) {
			$elements_manager->add_category( 'unysonplus-shop', array(
				'title' => __( 'Unyson+ Shop', 'fw' ),
				'icon'  => 'eicon-cart',
			) );
		}

		// The Animation Engine's elements, when that extension is active.
		if ( fw_ext( 'animation-engine' ) ) {
			$elements_manager->add_category( 'unysonplus-motion', array(
				'title' => __( 'Unyson+ Motion', 'fw' ),
				'icon'  => 'eicon-animation',
			) );
		}
	}

	/**
	 * Put the Unyson+ categories at the top of the widget panel (after Favorites).
	 *
	 * Elementor appends categories in registration order and has no API or filter to
	 * place one — `add_category()` only appends, and the document-config filter merges with
	 * array_replace_recursive(), which keeps the original key order. So the list is
	 * reordered in place, late on `categories_registered`, through reflection on the
	 * manager's `categories` property. Anything unexpected (a renamed property, a
	 * non-array) leaves Elementor's order untouched.
	 *
	 * @internal
	 */
	public function _action_order_categories( $elements_manager ) {
		try {
			$property = new ReflectionProperty( $elements_manager, 'categories' );
			$property->setAccessible( true );
			$categories = $property->getValue( $elements_manager );
		} catch ( Exception $e ) {
			return;
		}

		if ( ! is_array( $categories ) || ! isset( $categories['unysonplus'] ) ) {
			return;
		}

		$first = array();
		foreach ( array( 'favorites', 'unysonplus', 'unysonplus-shop', 'unysonplus-motion' ) as $key ) {
			if ( isset( $categories[ $key ] ) ) {
				$first[ $key ] = $categories[ $key ];
				unset( $categories[ $key ] );
			}
		}

		$property->setValue( $elements_manager, $first + $categories );
	}

	/** @internal */
	public function _action_register_widgets( $widgets_manager ) {
		if ( ! fw_ext( 'shortcodes' ) ) {
			return;
		}

		if ( ! $this->load_widget_classes() ) {
			return;
		}

		foreach ( $this->get_widget_files() as $tag => $widget ) {
			if ( '' !== $this->widget_for_shortcode( $tag ) && class_exists( $widget['class'] ) ) {
				$widgets_manager->register( new $widget['class']() );
			}
		}
	}

	/** @internal */
	public function _action_register_scripts() {
		wp_register_script(
			'fw-elementor-widgets',
			$this->get_uri( '/static/js/widgets.js' ),
			array( 'jquery', 'elementor-frontend' ),
			$this->manifest->get_version(),
			true
		);
	}

	/** Our registered widget instances. */
	private function get_widgets() {
		if ( ! class_exists( '\Elementor\Plugin' ) || ! class_exists( 'FW_Elementor_Shortcode_Widget' ) ) {
			return array();
		}

		$out = array();

		foreach ( (array) \Elementor\Plugin::$instance->widgets_manager->get_widget_types() as $name => $widget ) {
			if ( $widget instanceof FW_Elementor_Shortcode_Widget ) {
				$out[ $name ] = $widget;
			}
		}

		return $out;
	}

	/**
	 * The editor's preview frame. Widgets re-render there over AJAX as settings change,
	 * and anything those renders enqueue is discarded — so every widget's assets, and the
	 * stylesheet of every design it can switch to, load up front.
	 *
	 * @internal
	 */
	public function _action_preview_assets() {
		foreach ( $this->get_widgets() as $widget ) {
			$shortcode = $widget->get_shortcode();

			if ( ! $shortcode ) {
				continue;
			}

			$shortcode->_enqueue_static();
			$widget->enqueue_preview_static();

			if ( function_exists( 'fw_sc_designs' ) ) {
				foreach ( array_keys( (array) fw_sc_designs( $widget->shortcode_tag() ) ) as $design ) {
					$widget->enqueue_design( $design );
				}
			}
		}

		wp_enqueue_script( 'fw-elementor-widgets' );
	}

	/**
	 * Enqueue the assets of the widgets on the current document in <head>.
	 *
	 * A widget's render() also enqueues them, but that runs after wp_head, so the styles
	 * would print in the footer and the element would flash unstyled first. Reading the
	 * document's saved Elementor data here is a string search plus one decode, and only
	 * for documents that contain one of our widgets.
	 *
	 * @internal
	 */
	public function _action_document_assets() {
		if ( ! did_action( 'elementor/loaded' ) || ! is_singular() ) {
			return;
		}

		$data = get_post_meta( get_queried_object_id(), '_elementor_data', true );

		if ( ! is_string( $data ) || false === strpos( $data, '"widgetType":"up-' ) ) {
			return;
		}

		$elements = json_decode( $data, true );

		if ( ! is_array( $elements ) ) {
			return;
		}

		$widgets = $this->get_widgets();

		$walk = function ( $elements ) use ( &$walk, $widgets ) {
			foreach ( $elements as $element ) {
				if ( ! is_array( $element ) ) {
					continue;
				}

				if ( isset( $element['widgetType'], $widgets[ $element['widgetType'] ] ) ) {
					$widgets[ $element['widgetType'] ]->enqueue_for_settings( isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array() );
				}

				if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
					$walk( $element['elements'] );
				}
			}
		};

		$walk( $elements );
	}
}
