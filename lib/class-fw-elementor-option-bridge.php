<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Translates Unyson+ option schemas into Elementor controls, and Elementor's saved
 * control values back into shortcode atts.
 *
 * Both directions walk the same tree, so they cannot drift apart: a control is named
 * after its option's fw_akg PATH with `/` replaced by `__` (`design_settings/default/
 * items_per_slide` → `design_settings__default__items_per_slide`), and reading it back
 * is fw_aks() on that same path. Names stay unique even where a multi-picker repeats a
 * sub-option id under several choices (every carousel design has `carousel_autoplay`).
 *
 * Value shapes differ between the two systems, and every difference is handled in one
 * pair of functions — to_elementor() / from_elementor() — per option type:
 *
 *   switch          a string return_value or '' ↔ the option's right / left choice value
 *   upload          { id, url } ↔ { attachment_id, url }
 *   compact colour  two controls (preset select + custom colour) ↔ { predefined, custom }
 *   addable-popup   a REPEATER (one level — repeaters do not nest) ↔ a list of item arrays
 *   multi-picker    a picker control + its choices' controls, each gated by `condition`
 *
 * An option type with no mapping is skipped rather than guessed at; the shortcode's
 * declared default then applies, exactly as for an option the widget does not expose.
 */
class FW_Elementor_Option_Bridge {

	/**
	 * Hidden widget setting holding the RESIDUE: every att the controls cannot carry (an
	 * option the widget does not expose, a legacy key, a value whose type a control would
	 * normalise). Merged back under the controls' values at render, so a widget made from
	 * page-builder atts renders exactly what the page builder would.
	 */
	const EXTRA = 'up_extra_atts';

	/** The same, per repeater row. */
	const ITEM_EXTRA = 'up_item_extra';

	/**
	 * Shortcode atts (the shape the page builder stores) -> widget settings for the widget
	 * that renders that shortcode. The inverse of the widget's render path: rendering the
	 * returned settings reproduces the page-builder output.
	 *
	 * Works without the editor loaded (CLI / cron imports), but needs Elementor active.
	 *
	 * @param string $tag  Shortcode tag, e.g. 'testimonials'.
	 * @param array  $atts Page-builder atts.
	 * @return array Widget settings, or an empty array when there is no widget for $tag.
	 */
	public static function settings_from_atts( $tag, array $atts ) {
		$ext    = fw_ext( 'elementor' );
		$widget = $ext ? $ext->get_widget( $tag ) : null;

		return $widget ? $widget->settings_from_atts( $atts ) : array();
	}

	/**
	 * The leaf options of a schema, keyed by id. Descends through layout containers
	 * (tab / group / box), which do not namespace values, but stops at multi-pickers and
	 * addables — those are values in their own right.
	 *
	 * @param array $options A shortcode options tree.
	 * @return array id => option
	 */
	public static function leaves( $options ) {
		$found = array();

		foreach ( (array) $options as $id => $option ) {
			if ( ! is_array( $option ) ) {
				continue;
			}

			if ( isset( $option['options'] ) && is_array( $option['options'] ) && ( empty( $option['type'] ) || in_array( $option['type'], array( 'tab', 'group', 'box', 'popup' ), true ) ) ) {
				$found += self::leaves( $option['options'] );
				continue;
			}

			if ( ! empty( $option['type'] ) && ! isset( $found[ $id ] ) ) {
				$found[ $id ] = self::unwrap( $option );
			}
		}

		return $found;
	}

	/**
	 * A popover holding ONE inner option stores that option's value directly (flip box's
	 * Effect = an image-picker) — so it is that option, under the popover's label.
	 */
	private static function unwrap( array $option ) {
		if ( 'popover' !== $option['type'] || empty( $option['inner-options'] ) || ! is_array( $option['inner-options'] ) || 1 !== count( $option['inner-options'] ) ) {
			return $option;
		}

		$inner = current( $option['inner-options'] );

		if ( ! is_array( $inner ) || empty( $inner['type'] ) ) {
			return $option;
		}

		if ( isset( $option['label'] ) && is_string( $option['label'] ) ) {
			$inner['label'] = $option['label'];
		}
		if ( array_key_exists( 'value', $option ) ) {
			$inner['value'] = $option['value'];
		}

		return $inner;
	}

	/** The control name for an option path. */
	public static function name( $path ) {
		return str_replace( array( '/', '-' ), array( '__', '_' ), $path );
	}

	/**
	 * Add the controls for a list of options to a widget (or a Repeater).
	 *
	 * @param object $target    A Widget_Base or \Elementor\Repeater.
	 * @param array  $leaves    id => option, from leaves().
	 * @param array  $ids       Which ids to expose, in panel order.
	 * @param array  $overrides id => partial control args merged over the generated ones
	 *                          (a widget's own defaults, labels, repeater title_field…).
	 * @param string $prefix    Path prefix (for multi-picker choices).
	 * @param array  $condition Elementor condition inherited from an enclosing picker.
	 */
	public static function add_controls( $target, array $leaves, array $ids, array $overrides = array(), $prefix = '', array $condition = array() ) {
		foreach ( $ids as $id ) {
			if ( ! isset( $leaves[ $id ] ) ) {
				continue;
			}

			$option = $leaves[ $id ];
			$path   = $prefix . $id;

			if ( 'multi-picker' === $option['type'] ) {
				self::add_multi_picker( $target, $option, $path, $overrides, $condition );
				continue;
			}

			$override = isset( $overrides[ $path ] ) ? $overrides[ $path ] : array();

			foreach ( self::controls_for( self::name( $path ), $option, $override ) as $name => $args ) {
				if ( $condition ) {
					$args['condition'] = isset( $args['condition'] ) ? array_merge( $condition, $args['condition'] ) : $condition;
				}

				$target->add_control( $name, $args );
			}
		}
	}

	/**
	 * A multi-picker becomes its picker control followed by every choice's controls,
	 * each set gated on the picker's value. Nested pickers carry the whole chain of
	 * conditions down (Elementor ANDs the keys of one `condition`).
	 */
	private static function add_multi_picker( $target, array $option, $path, array $overrides, array $condition ) {
		if ( empty( $option['picker'] ) || ! is_array( $option['picker'] ) ) {
			return;
		}

		$picker_id   = key( $option['picker'] );
		$picker      = self::picker_with_default( $option );
		$picker_path = $path . '/' . $picker_id;

		self::add_controls( $target, array( $picker_id => $picker ), array( $picker_id ), $overrides, $path . '/', $condition );

		foreach ( (array) ( isset( $option['choices'] ) ? $option['choices'] : array() ) as $choice => $choice_options ) {
			$sub = self::leaves( $choice_options );

			if ( ! $sub ) {
				continue;
			}

			$gate = array_merge( $condition, array( self::name( $picker_path ) => self::to_elementor( $picker, $choice ) ) );

			self::add_controls( $target, $sub, array_keys( $sub ), $overrides, $path . '/' . $choice . '/', $gate );
		}
	}

	/**
	 * A multi-picker's picker option, with a default. The picker usually declares none —
	 * the multi-picker's own value carries it (`{ design: 'default' }`). Without one the
	 * picker control starts empty, no choice's condition is met, and Elementor reports
	 * every choice control as inactive.
	 */
	private static function picker_with_default( array $option ) {
		$picker_id = key( $option['picker'] );
		$picker    = current( $option['picker'] );

		if ( ! isset( $picker['value'] ) || '' === $picker['value'] ) {
			if ( isset( $option['value'][ $picker_id ] ) && '' !== $option['value'][ $picker_id ] ) {
				$picker['value'] = $option['value'][ $picker_id ];
			} elseif ( ! empty( $picker['choices'] ) && is_array( $picker['choices'] ) && ! isset( $picker['choices'][''] ) ) {
				$picker['value'] = key( $picker['choices'] );
			}
		}

		return $picker;
	}

	/**
	 * The Elementor control(s) for one option. Most options map to one control; a
	 * compact colour maps to two. Returns an empty array for an unmapped type.
	 *
	 * @return array name => control args
	 */
	public static function controls_for( $name, array $option, array $override = array() ) {
		$type  = $option['type'];
		$label = isset( $option['label'] ) && is_string( $option['label'] ) ? wp_strip_all_tags( $option['label'] ) : '';
		$args  = array( 'label' => $label );

		if ( ! empty( $option['desc'] ) && is_string( $option['desc'] ) ) {
			$args['description'] = wp_strip_all_tags( $option['desc'] );
		}

		$value = isset( $option['value'] ) ? $option['value'] : null;

		switch ( $type ) {
			case 'text':
			case 'short-text':
				$args['type'] = \Elementor\Controls_Manager::TEXT;
				$args['label_block'] = true;
				break;

			case 'textarea':
				$args['type'] = \Elementor\Controls_Manager::TEXTAREA;
				break;

			case 'wp-editor':
				$args['type'] = \Elementor\Controls_Manager::WYSIWYG;
				break;

			case 'number':
				$args['type'] = \Elementor\Controls_Manager::NUMBER;
				break;

			case 'slider':
			case 'short-slider':
				$props        = isset( $option['properties'] ) ? (array) $option['properties'] : array();
				$args['type'] = \Elementor\Controls_Manager::NUMBER;
				foreach ( array( 'min', 'max', 'step' ) as $k ) {
					if ( isset( $props[ $k ] ) ) {
						$args[ $k ] = $props[ $k ];
					}
				}
				break;

			case 'switch':
				$right = self::switch_choice( $option, 'right' );
				$left  = self::switch_choice( $option, 'left' );
				$args['type']         = \Elementor\Controls_Manager::SWITCHER;
				$args['return_value'] = self::switch_return_value( $option );
				$args['label_on']     = isset( $right['label'] ) ? wp_strip_all_tags( $right['label'] ) : __( 'Yes', 'fw' );
				$args['label_off']    = isset( $left['label'] ) ? wp_strip_all_tags( $left['label'] ) : __( 'No', 'fw' );
				break;

			case 'radio':
			case 'select':
			case 'short-select':
			case 'border-style-picker':
			case 'button-style-picker':
			case 'table-style-picker':
			case 'image-style-picker':
				$args['type']    = \Elementor\Controls_Manager::SELECT;
				$args['options'] = self::flat_choices( isset( $option['choices'] ) ? $option['choices'] : array() );
				break;

			case 'checkboxes':
			case 'multi-select':
				$args['type']        = \Elementor\Controls_Manager::SELECT2;
				$args['multiple']    = true;
				$args['label_block'] = true;
				$args['options']     = self::flat_choices( isset( $option['choices'] ) ? $option['choices'] : array() );
				break;

			case 'image-picker':
				$args = array_merge( $args, self::image_picker_control( $option ) );
				break;

			case 'upload':
				$args['type'] = \Elementor\Controls_Manager::MEDIA;
				break;

			case 'multi-upload':
				$args['type'] = \Elementor\Controls_Manager::GALLERY;
				break;

			case 'table':
				return self::table_controls( $name, $option, $label, $override );

			case 'form-builder':
				return self::form_controls( $name, $option, $label, $override );

			case 'color-picker':
			case 'rgba-color-picker':
				$args['type'] = \Elementor\Controls_Manager::COLOR;
				break;

			case 'datetime-picker':
				// Same `Y/m/d H:i` string the page builder's picker stores.
				$args['type']           = \Elementor\Controls_Manager::DATE_TIME;
				$args['picker_options'] = array( 'dateFormat' => 'Y/m/d H:i', 'enableTime' => true );
				$args['label_block']    = true;
				break;

			case 'icon':
				$args['type']        = \Elementor\Controls_Manager::ICONS;
				$args['label_block'] = true;
				$args['skin']        = 'inline';
				break;

			case 'predefined-colors-color-picker-compact':
				return self::compact_color_controls( $name, $option, $args, $override );

			case 'multi-inline':
				return self::multi_inline_controls( $name, $option, $label );

			case 'unit-input':
				$units                = ! empty( $option['units'] ) ? array_values( (array) $option['units'] ) : array( 'px' );
				$args['type']         = \Elementor\Controls_Manager::SLIDER;
				$args['size_units']   = $units;
				$args['range']        = array_fill_keys( $units, array( 'min' => 0, 'max' => 200, 'step' => 0.1 ) );
				$args['range']['px']  = array( 'min' => 0, 'max' => 400, 'step' => 1 );
				break;

			case 'addable-popup':
			case 'addable-box':
				$args = array_merge( $args, self::repeater_control( $option ) );
				break;

			default:
				return array();
		}

		if ( null !== $value ) {
			$args['default'] = self::to_elementor( $option, $value );
		}

		return array( $name => array_merge( $args, $override ) );
	}

	/** An addable list → a REPEATER whose fields are the item's own options. */
	private static function repeater_control( array $option ) {
		$item_options = isset( $option['popup-options'] ) ? $option['popup-options'] : ( isset( $option['box-options'] ) ? $option['box-options'] : array() );
		$leaves       = self::leaves( $item_options );
		$repeater     = new \Elementor\Repeater();

		foreach ( $leaves as $id => $field ) {
			// Repeaters do not nest. A list inside a list item rides in the row's residue.
			if ( ! self::is_row_field( $field ) ) {
				continue;
			}

			foreach ( self::controls_for( self::name( $id ), $field ) as $name => $args ) {
				$repeater->add_control( $name, $args );
			}
		}

		$repeater->add_control( self::ITEM_EXTRA, array( 'type' => \Elementor\Controls_Manager::HIDDEN, 'default' => '' ) );

		$title = '';
		foreach ( $leaves as $id => $field ) {
			if ( in_array( $field['type'], array( 'text', 'short-text' ), true ) ) {
				$title = '{{{ ' . self::name( $id ) . ' }}}';
				break;
			}
		}

		return array(
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => $title,
		);
	}

	/**
	 * An image-picker. Alignment pickers become Elementor's own icon toggle; pickers whose
	 * every choice has an image become a visual choice (the same thumbnails the page
	 * builder shows); anything else — or one with an empty key, which a visual choice
	 * cannot represent — a plain select.
	 */
	private static function image_picker_control( array $option ) {
		$choices = isset( $option['choices'] ) ? (array) $option['choices'] : array();

		if ( isset( $option['attr']['class'] ) && false !== strpos( (string) $option['attr']['class'], 'sc-alignment' ) ) {
			$icons = array(
				'left'   => 'eicon-text-align-left',
				'center' => 'eicon-text-align-center',
				'right'  => 'eicon-text-align-right',
			);
			$out = array();
			foreach ( $choices as $key => $choice ) {
				if ( isset( $icons[ $key ] ) ) {
					$out[ $key ] = array( 'title' => self::choice_label( $key, $choice ), 'icon' => $icons[ $key ] );
				}
			}

			return array( 'type' => \Elementor\Controls_Manager::CHOOSE, 'options' => $out, 'toggle' => true );
		}

		$visual = defined( '\Elementor\Controls_Manager::VISUAL_CHOICE' ) && ! isset( $choices[''] );
		$out    = array();

		foreach ( $choices as $key => $choice ) {
			$src = is_array( $choice ) && isset( $choice['small']['src'] ) ? $choice['small']['src'] : ( is_array( $choice ) && isset( $choice['small'] ) && is_string( $choice['small'] ) ? $choice['small'] : '' );

			if ( '' === $src ) {
				$visual = false;
			}

			$out[ $key ] = array( 'title' => self::choice_label( $key, $choice ), 'image' => $src );
		}

		if ( $visual ) {
			return array(
				'type'        => \Elementor\Controls_Manager::VISUAL_CHOICE,
				'options'     => $out,
				'columns'     => count( $out ) > 4 ? 2 : max( 1, count( $out ) ),
				'label_block' => true,
			);
		}

		return array(
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => wp_list_pluck( $out, 'title' ),
		);
	}

	/**
	 * A multi-inline is a row of named sub-fields stored as one map (`price` =
	 * `{ monthly, yearly }`). Each part becomes its own control, `<name>__<key>`.
	 */
	private static function multi_inline_controls( $name, array $option, $label ) {
		$out   = array();
		$value = isset( $option['value'] ) && is_array( $option['value'] ) ? $option['value'] : array();

		foreach ( self::multi_inline_parts( $option ) as $key => $part ) {
			$part['label'] = trim( $label . ' — ' . ( isset( $part['title'] ) ? $part['title'] : ucfirst( $key ) ), ' —' );
			if ( array_key_exists( $key, $value ) ) {
				$part['value'] = $value[ $key ];
			}
			$out += self::controls_for( $name . '__' . $key, $part );
		}

		return $out;
	}

	private static function multi_inline_parts( array $option ) {
		$parts = array();

		foreach ( (array) ( isset( $option['fw_multi_options'] ) ? $option['fw_multi_options'] : array() ) as $key => $part ) {
			// One level: a part that is itself composite would need names nested again.
			if ( is_array( $part ) && ! empty( $part['type'] ) && self::is_mapped( $part ) && ! self::is_composite( $part ) && ! in_array( $part['type'], array( 'addable-popup', 'addable-box', 'multi-picker' ), true ) ) {
				$parts[ $key ] = $part;
			}
		}

		return $parts;
	}

	/** Options stored as one map but edited as several controls. */
	private static function is_composite( array $option ) {
		return in_array( $option['type'], array( 'predefined-colors-color-picker-compact', 'multi-inline', 'table', 'form-builder' ), true );
	}

	/**
	 * A composite option's value from its controls' settings, or null when none of its
	 * controls has a value (the option was never set).
	 */
	private static function composite_from( array $option, $name, array $settings ) {
		if ( 'table' === $option['type'] ) {
			return self::table_from( $name, $settings );
		}

		if ( 'form-builder' === $option['type'] ) {
			return self::form_from( $name, $settings );
		}

		if ( 'predefined-colors-color-picker-compact' === $option['type'] ) {
			if ( ! isset( $settings[ $name ] ) && ! isset( $settings[ $name . '__custom' ] ) ) {
				return null;
			}
			return array(
				'predefined' => isset( $settings[ $name ] ) ? (string) $settings[ $name ] : '',
				'custom'     => isset( $settings[ $name . '__custom' ] ) ? (string) $settings[ $name . '__custom' ] : '',
			);
		}

		$value = array();

		foreach ( self::multi_inline_parts( $option ) as $key => $part ) {
			if ( isset( $settings[ $name . '__' . $key ] ) ) {
				$value[ $key ] = self::from_elementor( $part, $settings[ $name . '__' . $key ] );
			}
		}

		return $value ? $value : null;
	}

	/** A composite option's value as its controls' settings. */
	private static function composite_to( array $option, $name, $value ) {
		if ( 'table' === $option['type'] ) {
			return self::table_to( $name, $value );
		}

		if ( 'form-builder' === $option['type'] ) {
			return self::form_to( $name, $value );
		}

		if ( 'predefined-colors-color-picker-compact' === $option['type'] ) {
			return array(
				$name             => is_array( $value ) && isset( $value['predefined'] ) ? $value['predefined'] : '',
				$name . '__custom' => is_array( $value ) && isset( $value['custom'] ) ? $value['custom'] : '',
			);
		}

		$out = array();

		foreach ( self::multi_inline_parts( $option ) as $key => $part ) {
			if ( is_array( $value ) && array_key_exists( $key, $value ) ) {
				$out[ $name . '__' . $key ] = self::to_elementor( $part, $value[ $key ] );
			}
		}

		return $out;
	}

	/** Field types whose options carry these keys. */
	private static $form_required    = array( 'text', 'textarea', 'number', 'email', 'website', 'checkboxes', 'radio', 'select', 'file-upload', 'date', 'rating', 'consent' );
	private static $form_placeholder = array( 'text', 'textarea', 'number', 'email', 'website' );
	private static $form_choices     = array( 'checkboxes', 'radio', 'select' );

	/**
	 * The form builder's value — `{ json: "[ {type, shortcode, width, options}, … ]" }` — as a
	 * repeater of fields: type, label (a heading's title), sub-title, required, placeholder,
	 * choices (one per line) and width. Everything else a field carries (validation
	 * constraints, help text, conditional logic, date / rating / file settings) rides in the
	 * row's residue and keeps working; it is edited in the page builder's form builder.
	 */
	private static function form_controls( $name, array $option, $label, array $override = array() ) {
		$types = array();

		try {
			$builder = fw()->backend->option_type( 'form-builder' );
			$method  = new ReflectionMethod( $builder, 'get_item_types' );
			$method->setAccessible( true );
			foreach ( (array) $method->invoke( $builder ) as $id => $item ) {
				$l10n         = method_exists( $item, 'get_item_localization' ) ? $item->get_item_localization() : array();
				$types[ $id ] = isset( $l10n['l10n']['item_title'] ) ? wp_strip_all_tags( $l10n['l10n']['item_title'] ) : ucwords( str_replace( '-', ' ', $id ) );
			}
		} catch ( Exception $e ) {
			$types = array();
		}

		if ( ! $types ) {
			$types = array( 'text' => 'Text', 'email' => 'Email', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'checkboxes' => 'Checkboxes', 'radio' => 'Radio', 'consent' => 'Consent', 'form-header-title' => 'Title' );
		}

		$widths = array( '' => __( 'Full width', 'fw' ) );
		if ( function_exists( 'fw_ext_builder_get_item_width' ) ) {
			foreach ( (array) fw_ext_builder_get_item_width( 'form-builder' ) as $key => $w ) {
				$widths[ $key ] = is_array( $w ) && isset( $w['title'] ) ? $w['title'] : $key;
			}
		}

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'field_type', array( 'label' => __( 'Field Type', 'fw' ), 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $types, 'default' => 'text' ) );
		$repeater->add_control( 'label', array( 'label' => __( 'Label / Title', 'fw' ), 'type' => \Elementor\Controls_Manager::TEXT, 'label_block' => true ) );
		$repeater->add_control( 'subtitle', array( 'label' => __( 'Sub-title', 'fw' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'field_type' => 'form-header-title' ) ) );
		$repeater->add_control( 'required', array( 'label' => __( 'Required', 'fw' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'field_type' => self::$form_required ) ) );
		$repeater->add_control( 'placeholder', array( 'label' => __( 'Placeholder', 'fw' ), 'type' => \Elementor\Controls_Manager::TEXT, 'condition' => array( 'field_type' => self::$form_placeholder ) ) );
		$repeater->add_control( 'choices', array( 'label' => __( 'Choices', 'fw' ), 'description' => __( 'One per line.', 'fw' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'condition' => array( 'field_type' => self::$form_choices ) ) );
		$repeater->add_control( 'width', array( 'label' => __( 'Width', 'fw' ), 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $widths, 'default' => '' ) );
		$repeater->add_control( self::ITEM_EXTRA, array( 'type' => \Elementor\Controls_Manager::HIDDEN, 'default' => '' ) );

		$value    = isset( $option['value'] ) && is_array( $option['value'] ) ? $option['value'] : array();
		$defaults = self::form_to( $name, $value );

		return array(
			$name . '__fields' => array_merge( array(
				'label'       => '' !== $label ? $label : __( 'Fields', 'fw' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label || field_type }}}',
				'default'     => isset( $defaults[ $name . '__fields' ] ) ? $defaults[ $name . '__fields' ] : array(),
			), $override ),
		);
	}

	/** One repeater row -> the field item it describes. */
	private static function form_item_from( array $row, $index ) {
		$type = isset( $row['field_type'] ) && '' !== $row['field_type'] ? (string) $row['field_type'] : 'text';
		$opts = array();

		if ( 'form-header-title' === $type ) {
			$opts['title']    = isset( $row['label'] ) ? (string) $row['label'] : '';
			$opts['subtitle'] = isset( $row['subtitle'] ) ? (string) $row['subtitle'] : '';
		} else {
			$opts['label'] = isset( $row['label'] ) ? (string) $row['label'] : '';
			if ( in_array( $type, self::$form_required, true ) && array_key_exists( 'required', $row ) ) {
				$opts['required'] = ( 'yes' === $row['required'] || true === $row['required'] );
			}
			if ( in_array( $type, self::$form_placeholder, true ) && array_key_exists( 'placeholder', $row ) ) {
				$opts['placeholder'] = (string) $row['placeholder'];
			}
			if ( in_array( $type, self::$form_choices, true ) && array_key_exists( 'choices', $row ) ) {
				$opts['choices'] = array_values( array_filter( array_map( 'trim', explode( "\n", str_replace( "\r\n", "\n", (string) $row['choices'] ) ) ), 'strlen' ) );
			}
		}

		// The field's name in submissions. A field carried in keeps its own (from the
		// residue); one added in the panel takes the page builder's `type_slug` shape.
		$slug = ! empty( $row['_id'] ) ? (string) $row['_id'] : substr( md5( $type . $index . ( isset( $row['label'] ) ? $row['label'] : '' ) ), 0, 7 );
		$item = array(
			'type'      => $type,
			'shortcode' => 'form-header-title' === $type ? 'form_header_title' : str_replace( '-', '_', $type ) . '_' . $slug,
			'width'     => isset( $row['width'] ) ? (string) $row['width'] : '',
			'options'   => $opts,
		);

		$rest = isset( $row[ self::ITEM_EXTRA ] ) ? self::decode_residue( $row[ self::ITEM_EXTRA ] ) : null;

		return null === $rest ? $item : self::merge_residue( $item, $rest );
	}

	private static function form_to( $name, $value ) {
		$items = is_array( $value ) && isset( $value['json'] ) && is_string( $value['json'] ) ? json_decode( $value['json'], true ) : array();
		$rows  = array();

		foreach ( is_array( $items ) ? array_values( $items ) : array() as $i => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$type = isset( $item['type'] ) ? (string) $item['type'] : 'text';
			$opts = isset( $item['options'] ) && is_array( $item['options'] ) ? $item['options'] : array();
			$row  = array( 'field_type' => $type, 'width' => isset( $item['width'] ) ? (string) $item['width'] : '' );

			if ( 'form-header-title' === $type ) {
				$row['label']    = isset( $opts['title'] ) ? (string) $opts['title'] : '';
				$row['subtitle'] = isset( $opts['subtitle'] ) ? (string) $opts['subtitle'] : '';
			} else {
				$row['label'] = isset( $opts['label'] ) ? (string) $opts['label'] : '';
				if ( array_key_exists( 'required', $opts ) ) {
					$row['required'] = $opts['required'] ? 'yes' : '';
				}
				if ( array_key_exists( 'placeholder', $opts ) ) {
					$row['placeholder'] = (string) $opts['placeholder'];
				}
				if ( array_key_exists( 'choices', $opts ) && is_array( $opts['choices'] ) ) {
					$row['choices'] = implode( "\n", array_map( 'strval', $opts['choices'] ) );
				}
			}

			$rest = self::residue( $item, self::form_item_from( $row, $i ) );
			$row[ self::ITEM_EXTRA ] = null === $rest ? '' : wp_json_encode( $rest, JSON_PRESERVE_ZERO_FRACTION );
			$rows[] = $row;
		}

		return array( $name . '__fields' => $rows );
	}

	private static function form_from( $name, array $settings ) {
		if ( ! isset( $settings[ $name . '__fields' ] ) ) {
			return null;
		}

		$items = array();
		foreach ( (array) $settings[ $name . '__fields' ] as $i => $row ) {
			if ( is_array( $row ) ) {
				$items[] = self::form_item_from( $row, $i );
			}
		}

		return array( 'json' => wp_json_encode( $items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION ) );
	}

	/**
	 * The table editor's data — `{ header_options, cols, rows, content }`, a grid of cells
	 * — as a repeater of rows: each row has its type and its cells, one per line. Header
	 * options and the column list ride in a hidden setting; the column list follows the
	 * widest row, so adding a cell to a row adds a column.
	 *
	 * A row whose cells carry more than text (a pricing amount, a button, a merged cell, a
	 * line break inside a cell) keeps them in the row's residue, so it still renders exactly;
	 * its cells are then edited in the table editor of the page builder, not here.
	 */
	private static function table_controls( $name, array $option, $label, array $override = array() ) {
		$repeater = new \Elementor\Repeater();

		$repeater->add_control( 'row', array(
			'label'   => __( 'Row Type', 'fw' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'heading-row' => __( 'Heading row', 'fw' ),
				'default-row' => __( 'Default row', 'fw' ),
				'pricing-row' => __( 'Pricing row', 'fw' ),
				'button-row'  => __( 'Button row', 'fw' ),
				'switch-row'  => __( 'Row switch', 'fw' ),
			),
			'default' => 'default-row',
		) );
		$repeater->add_control( 'cells', array(
			'label'       => __( 'Cells', 'fw' ),
			'description' => __( 'One cell per line.', 'fw' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'rows'        => 4,
		) );
		$repeater->add_control( self::ITEM_EXTRA, array( 'type' => \Elementor\Controls_Manager::HIDDEN, 'default' => '' ) );

		$value    = isset( $option['value'] ) && is_array( $option['value'] ) ? $option['value'] : array();
		$defaults = self::table_to( $name, $value );

		return array(
			$name . '__meta' => array(
				'type'    => \Elementor\Controls_Manager::HIDDEN,
				'default' => isset( $defaults[ $name . '__meta' ] ) ? $defaults[ $name . '__meta' ] : '',
			),
			$name . '__rows' => array_merge( array(
				'label'       => '' !== $label ? $label : __( 'Rows', 'fw' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ ( cells || "" ).split( "\\n" ).join( " | " ) || "Row" }}}',
				'default'     => isset( $defaults[ $name . '__rows' ] ) ? $defaults[ $name . '__rows' ] : array(),
			), $override ),
		);
	}

	/** A cell built from its text, with the editor's other cell fields at their defaults. */
	private static function table_cell( $text ) {
		return array( 'textarea' => (string) $text, 'amount' => '', 'description' => '', 'switch' => 'no', 'button' => '' );
	}

	/** One repeater row -> `{ row, cells }`, the row's slice of the table data. */
	private static function table_row_from( array $row ) {
		$cells = array();

		foreach ( explode( "\n", str_replace( "\r\n", "\n", isset( $row['cells'] ) ? (string) $row['cells'] : '' ) ) as $text ) {
			$cells[] = self::table_cell( $text );
		}

		$slice = array(
			'row'   => array( 'name' => isset( $row['row'] ) && '' !== $row['row'] ? (string) $row['row'] : 'default-row' ),
			'cells' => $cells,
		);

		$rest = isset( $row[ self::ITEM_EXTRA ] ) ? self::decode_residue( $row[ self::ITEM_EXTRA ] ) : null;

		return null === $rest ? $slice : self::merge_residue( $slice, $rest );
	}

	private static function table_leading_headers( array $rows ) {
		$count = 0;
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['name'] ) || 'heading-row' !== $row['name'] ) {
				break;
			}
			$count++;
		}
		return $count;
	}

	private static function table_to( $name, $value ) {
		$value   = is_array( $value ) ? $value : array();
		$rows    = isset( $value['rows'] ) && is_array( $value['rows'] ) ? array_values( $value['rows'] ) : array();
		$content = isset( $value['content'] ) && is_array( $value['content'] ) ? array_values( $value['content'] ) : array();
		$header  = isset( $value['header_options'] ) && is_array( $value['header_options'] ) ? $value['header_options'] : array();

		$meta = array(
			'header_options' => $header,
			'cols'           => isset( $value['cols'] ) && is_array( $value['cols'] ) ? array_values( $value['cols'] ) : array(),
			// The header row count is re-derived from the rows when it was derived that way to
			// begin with, so changing a row's type in the panel moves the header with it.
			'auto_header'    => isset( $header['header_rows'] ) && (int) $header['header_rows'] === self::table_leading_headers( $rows ),
		);

		$out = array();

		foreach ( $rows as $i => $row ) {
			$cells = isset( $content[ $i ] ) && is_array( $content[ $i ] ) ? array_values( $content[ $i ] ) : array();
			$texts = array();
			foreach ( $cells as $cell ) {
				$texts[] = is_array( $cell ) && isset( $cell['textarea'] ) ? (string) $cell['textarea'] : '';
			}

			$setting = array(
				'row'   => is_array( $row ) && isset( $row['name'] ) ? (string) $row['name'] : 'default-row',
				'cells' => implode( "\n", $texts ),
			);
			$rest = self::residue( array( 'row' => $row, 'cells' => $cells ), self::table_row_from( $setting ) );
			$setting[ self::ITEM_EXTRA ] = null === $rest ? '' : wp_json_encode( $rest, JSON_PRESERVE_ZERO_FRACTION );

			$out[] = $setting;
		}

		return array(
			$name . '__meta' => wp_json_encode( $meta, JSON_PRESERVE_ZERO_FRACTION ),
			$name . '__rows' => $out,
		);
	}

	private static function table_from( $name, array $settings ) {
		if ( ! isset( $settings[ $name . '__rows' ] ) && ! isset( $settings[ $name . '__meta' ] ) ) {
			return null;
		}

		$meta    = isset( $settings[ $name . '__meta' ] ) ? self::decode_residue( $settings[ $name . '__meta' ] ) : null;
		$meta    = is_array( $meta ) ? $meta : array();
		$rows    = array();
		$content = array();
		$width   = 0;

		foreach ( (array) ( isset( $settings[ $name . '__rows' ] ) ? $settings[ $name . '__rows' ] : array() ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$slice     = self::table_row_from( $row );
			$rows[]    = isset( $slice['row'] ) ? $slice['row'] : array( 'name' => 'default-row' );
			$content[] = isset( $slice['cells'] ) && is_array( $slice['cells'] ) ? array_values( $slice['cells'] ) : array();
			$width     = max( $width, count( end( $content ) ) );
		}

		// The column list follows the widest row: kept as saved, padded or trimmed to fit.
		$cols = isset( $meta['cols'] ) && is_array( $meta['cols'] ) ? array_values( $meta['cols'] ) : array();
		while ( count( $cols ) < $width ) {
			$cols[] = array( 'name' => 'default-col' );
		}
		if ( $width && count( $cols ) > $width ) {
			$cols = array_slice( $cols, 0, $width );
		}

		// The table option declares no default value, so a new widget's meta carries empty
		// header options — and without a purpose the table view renders nothing.
		$header = array_merge(
			array( 'table_purpose' => 'tabular', 'footer_rows' => 0 ),
			isset( $meta['header_options'] ) && is_array( $meta['header_options'] ) ? $meta['header_options'] : array()
		);
		if ( ! empty( $meta['auto_header'] ) || ! isset( $header['header_rows'] ) ) {
			$header['header_rows'] = self::table_leading_headers( $rows );
		}

		return array(
			'header_options' => $header,
			'cols'           => $cols,
			'rows'           => $rows,
			'content'        => $content,
		);
	}

	/**
	 * A compact colour is a { predefined, custom } pair: the site palette preset (a class)
	 * or a custom colour (an inline style). It becomes a preset select plus a colour
	 * control. Both are always shown: the view reads both halves, so hiding one would let
	 * Elementor drop its value as an inactive control and change the output.
	 */
	private static function compact_color_controls( $name, array $option, array $args, array $override ) {
		$presets = array( '' => __( 'Default', 'fw' ) );

		foreach ( (array) ( isset( $option['choices'] ) ? $option['choices'] : array() ) as $key => $choice ) {
			$presets[ $key ] = self::choice_label( $key, $choice );
		}

		$value = isset( $option['value'] ) && is_array( $option['value'] ) ? $option['value'] : array();

		return array(
			$name => array_merge(
				$args,
				array(
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $presets,
					'default' => isset( $value['predefined'] ) ? (string) $value['predefined'] : '',
				),
				$override
			),
			$name . '__custom' => array(
				'label'     => __( 'Custom Color', 'fw' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => isset( $value['custom'] ) ? (string) $value['custom'] : '',
			),
		);
	}

	/**
	 * Read the atts for a list of options out of Elementor settings — the inverse of
	 * add_controls(), walking the same tree.
	 *
	 * @param array  $settings Elementor settings (get_settings_for_display()).
	 * @param array  $leaves   id => option.
	 * @param array  $ids      The exposed ids.
	 * @param array  $atts     Atts to write into (by fw_aks path).
	 * @param string $prefix   Path prefix.
	 * @return array
	 */
	public static function to_atts( array $settings, array $leaves, array $ids, array $atts = array(), $prefix = '' ) {
		foreach ( $ids as $id ) {
			if ( ! isset( $leaves[ $id ] ) ) {
				continue;
			}

			$option = $leaves[ $id ];
			$path   = $prefix . $id;

			if ( 'multi-picker' === $option['type'] ) {
				if ( empty( $option['picker'] ) || ! is_array( $option['picker'] ) ) {
					continue;
				}

				$picker_id = key( $option['picker'] );
				$atts      = self::to_atts( $settings, array( $picker_id => self::picker_with_default( $option ) ), array( $picker_id ), $atts, $path . '/' );

				foreach ( (array) ( isset( $option['choices'] ) ? $option['choices'] : array() ) as $choice => $choice_options ) {
					$sub = self::leaves( $choice_options );
					if ( $sub ) {
						$atts = self::to_atts( $settings, $sub, array_keys( $sub ), $atts, $path . '/' . $choice . '/' );
					}
				}
				continue;
			}

			$name = self::name( $path );

			if ( self::is_composite( $option ) ) {
				$value = self::composite_from( $option, $name, $settings );
				if ( null !== $value ) {
					fw_aks( $path, $value, $atts );
				}
				continue;
			}

			// Null = a control whose condition is not met (another design's options); leave
			// it to the shortcode default rather than reading it as "off" / empty.
			if ( ! isset( $settings[ $name ] ) || ! self::is_mapped( $option ) ) {
				continue;
			}

			fw_aks( $path, self::from_elementor( $option, $settings[ $name ] ), $atts );
		}

		return $atts;
	}

	/**
	 * Atts -> settings for a list of options: the inverse of to_atts(). Only values present
	 * in $atts are written, so to_atts() of the result yields no keys $atts did not have.
	 */
	public static function to_settings( array $atts, array $leaves, array $ids, array $settings = array(), $prefix = '' ) {
		foreach ( $ids as $id ) {
			if ( ! isset( $leaves[ $id ] ) || ! self::is_mapped( $leaves[ $id ] ) ) {
				continue;
			}

			$option = $leaves[ $id ];
			$path   = $prefix . $id;

			if ( ! self::has_path( $atts, $path ) ) {
				continue;
			}

			if ( 'multi-picker' === $option['type'] ) {
				if ( empty( $option['picker'] ) || ! is_array( $option['picker'] ) ) {
					continue;
				}

				$picker_id = key( $option['picker'] );
				$settings  = self::to_settings( $atts, array( $picker_id => self::picker_with_default( $option ) ), array( $picker_id ), $settings, $path . '/' );

				foreach ( (array) ( isset( $option['choices'] ) ? $option['choices'] : array() ) as $choice => $choice_options ) {
					$sub = self::leaves( $choice_options );
					if ( $sub ) {
						$settings = self::to_settings( $atts, $sub, array_keys( $sub ), $settings, $path . '/' . $choice . '/' );
					}
				}
				continue;
			}

			$name  = self::name( $path );
			$value = fw_akg( $path, $atts );

			if ( self::is_composite( $option ) ) {
				$settings = array_merge( $settings, self::composite_to( $option, $name, $value ) );
				continue;
			}

			$settings[ $name ] = self::to_elementor( $option, $value );
		}

		return $settings;
	}

	/** Whether $path (fw_akg syntax) exists in $atts, even holding null or ''. */
	private static function has_path( array $atts, $path ) {
		$node = $atts;

		foreach ( explode( '/', $path ) as $key ) {
			if ( ! is_array( $node ) || ! array_key_exists( $key, $node ) ) {
				return false;
			}
			$node = $node[ $key ];
		}

		return true;
	}

	private static function is_map( $value ) {
		return is_array( $value ) && array() !== $value && array_keys( $value ) !== range( 0, count( $value ) - 1 );
	}

	/**
	 * What $original holds that $rebuilt lacks or holds differently. Maps are compared key
	 * by key; anything else (scalars, lists) is compared whole. Null when nothing differs.
	 */
	public static function residue( $original, $rebuilt ) {
		if ( self::is_map( $original ) && self::is_map( $rebuilt ) ) {
			$out = array();

			foreach ( $original as $key => $value ) {
				if ( ! array_key_exists( $key, $rebuilt ) ) {
					$out[ $key ] = null === $value ? array( '__null' => true ) : $value;
					continue;
				}

				$diff = self::residue( $value, $rebuilt[ $key ] );

				if ( null !== $diff ) {
					$out[ $key ] = $diff;
				}
			}

			return $out ? $out : null;
		}

		if ( $original === $rebuilt || self::same_json( $original, $rebuilt ) ) {
			return null;
		}

		return null === $original ? array( '__null' => true ) : $original;
	}

	/**
	 * Two strings holding the same JSON document. A value stored as a JSON string (the form
	 * builder's) is re-encoded when rebuilt, and another encoder's key order or escaping must
	 * not read as a change — that would put the whole document in the residue, where it
	 * would override every later edit made in the panel.
	 */
	private static function same_json( $a, $b ) {
		if ( ! is_string( $a ) || ! is_string( $b ) || '' === $a || '' === $b || ! in_array( $a[0], array( '[', '{' ), true ) ) {
			return false;
		}

		$x = json_decode( $a, true );
		$y = json_decode( $b, true );

		return is_array( $x ) && is_array( $y ) && self::canonical( $x ) === self::canonical( $y );
	}

	private static function canonical( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}
		if ( self::is_map( $value ) ) {
			ksort( $value );
		}
		return array_map( array( __CLASS__, 'canonical' ), $value );
	}

	/** Lay a residue back over rebuilt values (the inverse of residue()). */
	public static function merge_residue( $rebuilt, $residue ) {
		if ( array( '__null' => true ) === $residue ) {
			return null;
		}

		if ( ! self::is_map( $residue ) || ! is_array( $rebuilt ) || ( $rebuilt && ! self::is_map( $rebuilt ) ) ) {
			return $residue;
		}

		foreach ( $residue as $key => $value ) {
			$rebuilt[ $key ] = self::merge_residue( array_key_exists( $key, $rebuilt ) ? $rebuilt[ $key ] : array(), $value );
		}

		return $rebuilt;
	}

	/** A residue stored in a settings string, decoded. */
	public static function decode_residue( $json ) {
		$value = is_string( $json ) && '' !== $json ? json_decode( $json, true ) : null;
		return is_array( $value ) ? $value : null;
	}

	/** An option value in the shape its Elementor control stores. */
	public static function to_elementor( array $option, $value ) {
		switch ( $option['type'] ) {
			case 'switch':
				$right = self::switch_choice( $option, 'right' );
				return ( isset( $right['value'] ) && $value === $right['value'] ) ? self::switch_return_value( $option ) : '';

			case 'upload':
				return array(
					'id'  => is_array( $value ) && isset( $value['attachment_id'] ) ? $value['attachment_id'] : '',
					'url' => is_array( $value ) && isset( $value['url'] ) ? (string) $value['url'] : '',
				);

			case 'addable-popup':
			case 'addable-box':
				$item_options = isset( $option['popup-options'] ) ? $option['popup-options'] : ( isset( $option['box-options'] ) ? $option['box-options'] : array() );
				$leaves       = self::leaves( $item_options );
				$out          = array();

				foreach ( (array) $value as $item ) {
					$row = array();
					foreach ( $leaves as $id => $field ) {
						if ( ! is_array( $item ) || ! array_key_exists( $id, $item ) || ! self::is_row_field( $field ) ) {
							continue;
						}
						if ( self::is_composite( $field ) ) {
							$row = array_merge( $row, self::composite_to( $field, self::name( $id ), $item[ $id ] ) );
						} else {
							$row[ self::name( $id ) ] = self::to_elementor( $field, $item[ $id ] );
						}
					}

					// Whatever the row's fields cannot carry rides along in the row.
					$rebuilt = self::from_elementor( $option, array( $row ) );
					$rest    = self::residue( $item, isset( $rebuilt[0] ) ? $rebuilt[0] : array() );
					$row[ self::ITEM_EXTRA ] = null === $rest ? '' : wp_json_encode( $rest, JSON_PRESERVE_ZERO_FRACTION );

					$out[] = $row;
				}

				return $out;

			case 'multi-select':
				return array_values( (array) $value );

			case 'checkboxes':
				// Stored as { key: true } for the ticked choices.
				$out = array();
				foreach ( (array) $value as $key => $on ) {
					if ( $on && 'false' !== $on ) {
						$out[] = (string) $key;
					}
				}
				return $out;

			case 'multi-upload':
				$out = array();
				foreach ( (array) $value as $image ) {
					if ( is_array( $image ) && ! empty( $image['url'] ) ) {
						$out[] = array( 'id' => isset( $image['attachment_id'] ) ? $image['attachment_id'] : '', 'url' => (string) $image['url'] );
					}
				}
				return $out;

			case 'unit-input':
				return array(
					'size' => is_array( $value ) && isset( $value['value'] ) ? $value['value'] : '',
					'unit' => is_array( $value ) && isset( $value['unit'] ) ? $value['unit'] : 'px',
				);

			case 'icon':
				// A font icon is the one kind the Icons control can show. An emoji, Lottie, Rive
				// or inline SVG has no equivalent there; it stays in the residue and still renders.
				if ( is_string( $value ) ) {
					$value = '' === $value ? array( 'type' => 'none' ) : array( 'type' => 'icon-font', 'icon-class' => $value );
				}
				if ( is_array( $value ) && isset( $value['type'], $value['icon-class'] ) && 'icon-font' === $value['type'] && '' !== trim( (string) $value['icon-class'] ) ) {
					return array( 'value' => (string) $value['icon-class'], 'library' => self::icon_library( (string) $value['icon-class'] ) );
				}
				return array( 'value' => '', 'library' => '' );

			default:
				return is_scalar( $value ) ? $value : '';
		}
	}

	/** The Icons-control library a font-icon class belongs to (cosmetic: it labels the pick). */
	private static function icon_library( $class ) {
		if ( preg_match( '/(?:^|\s)fab(?:\s|$)|fa-brands/', $class ) ) {
			return 'fa-brands';
		}
		if ( preg_match( '/(?:^|\s)far(?:\s|$)|fa-regular/', $class ) ) {
			return 'fa-regular';
		}
		if ( preg_match( '/(?:^|\s)(?:fa|fas)(?:\s|$)|fa-solid/', $class ) ) {
			return 'fa-solid';
		}
		return 'unysonplus';
	}

	/** An Elementor control value in the shape the shortcode expects. */
	public static function from_elementor( array $option, $value ) {
		switch ( $option['type'] ) {
			case 'switch':
				$right = self::switch_choice( $option, 'right' );
				$left  = self::switch_choice( $option, 'left' );
				$off   = isset( $left['value'] ) ? $left['value'] : '';
				// SWITCHER stores '' for off; a setting written by hand may carry the option's
				// own off value ('no', false) instead — both mean off.
				if ( '' === $value || null === $value || false === $value || $value === $off ) {
					return $off;
				}
				return isset( $right['value'] ) ? $right['value'] : $value;

			case 'upload':
				$url = is_array( $value ) && ! empty( $value['url'] ) ? (string) $value['url'] : '';
				if ( '' === $url ) {
					return '';
				}
				return array(
					'attachment_id' => isset( $value['id'] ) ? (int) $value['id'] : 0,
					'url'           => $url,
				);

			case 'slider':
			case 'short-slider':
			case 'number':
				return null === $value ? '' : $value;

			case 'multi-select':
				return array_values( array_filter( (array) $value, 'strlen' ) );

			case 'checkboxes':
				$out = array();
				foreach ( (array) $value as $key ) {
					if ( '' !== (string) $key ) {
						$out[ (string) $key ] = true;
					}
				}
				return $out;

			case 'multi-upload':
				$out = array();
				foreach ( (array) $value as $image ) {
					if ( is_array( $image ) && ! empty( $image['url'] ) ) {
						$out[] = array( 'attachment_id' => isset( $image['id'] ) ? $image['id'] : '', 'url' => (string) $image['url'] );
					}
				}
				return $out;

			case 'unit-input':
				return array(
					'value' => is_array( $value ) && isset( $value['size'] ) ? $value['size'] : '',
					'unit'  => is_array( $value ) && ! empty( $value['unit'] ) ? $value['unit'] : 'px',
				);

			case 'icon':
				// An SVG picked from the media library renders as an uploaded image icon.
				if ( is_array( $value ) && isset( $value['library'] ) && 'svg' === $value['library'] && ! empty( $value['value']['url'] ) ) {
					return array(
						'type'          => 'custom-upload',
						'attachment-id' => isset( $value['value']['id'] ) ? $value['value']['id'] : '',
						'url'           => (string) $value['value']['url'],
					);
				}
				if ( is_array( $value ) && isset( $value['value'] ) && is_string( $value['value'] ) && '' !== $value['value'] ) {
					return array( 'type' => 'icon-font', 'icon-class' => $value['value'] );
				}
				return array( 'type' => 'none' );

			case 'addable-popup':
			case 'addable-box':
				$item_options = isset( $option['popup-options'] ) ? $option['popup-options'] : ( isset( $option['box-options'] ) ? $option['box-options'] : array() );
				$leaves       = self::leaves( $item_options );
				$out          = array();

				foreach ( (array) $value as $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}
					$item = array();
					foreach ( $leaves as $id => $field ) {
						$name = self::name( $id );
						if ( ! self::is_row_field( $field ) ) {
							continue;
						}
						if ( self::is_composite( $field ) ) {
							$composite = self::composite_from( $field, $name, $row );
							if ( null !== $composite ) {
								$item[ $id ] = $composite;
							}
						} elseif ( array_key_exists( $name, $row ) ) {
							$item[ $id ] = self::from_elementor( $field, $row[ $name ] );
						}
					}
					$rest  = isset( $row[ self::ITEM_EXTRA ] ) ? self::decode_residue( $row[ self::ITEM_EXTRA ] ) : null;
					$out[] = null === $rest ? $item : self::merge_residue( $item, $rest );
				}

				return $out;

			default:
				return $value;
		}
	}

	/** Whether a field can be a repeater row control (repeaters do not nest). */
	private static function is_row_field( array $field ) {
		return self::is_mapped( $field ) && ! in_array( $field['type'], array( 'addable-popup', 'addable-box', 'multi-picker' ), true );
	}

	/** Whether an option type has a control mapping (see controls_for()). */
	public static function is_mapped( array $option ) {
		return in_array( $option['type'], array(
			'text', 'short-text', 'textarea', 'wp-editor', 'number', 'slider', 'short-slider', 'switch',
			'select', 'short-select', 'border-style-picker', 'multi-select', 'image-picker', 'upload',
			'color-picker', 'rgba-color-picker', 'predefined-colors-color-picker-compact', 'icon',
			'multi-inline', 'unit-input', 'radio', 'checkboxes', 'datetime-picker', 'button-style-picker', 'table-style-picker', 'image-style-picker', 'multi-upload', 'table', 'form-builder',
			'addable-popup', 'addable-box', 'multi-picker',
		), true );
	}

	private static function switch_choice( array $option, $side ) {
		$key = $side . '-choice';
		return isset( $option[ $key ] ) && is_array( $option[ $key ] ) ? $option[ $key ] : array( 'value' => 'right' === $side ? 'yes' : 'no' );
	}

	/** SWITCHER stores a string; a switch whose "on" value is not one stores 'yes'. */
	private static function switch_return_value( array $option ) {
		$right = self::switch_choice( $option, 'right' );
		return ( isset( $right['value'] ) && is_string( $right['value'] ) && '' !== $right['value'] ) ? $right['value'] : 'yes';
	}

	/** Select choices, with optgroups flattened into one list. */
	private static function flat_choices( $choices ) {
		$out = array();

		foreach ( (array) $choices as $key => $choice ) {
			if ( is_array( $choice ) && isset( $choice['choices'] ) && is_array( $choice['choices'] ) ) {
				$out += self::flat_choices( $choice['choices'] );
				continue;
			}

			$out[ $key ] = self::choice_label( $key, $choice );
		}

		return $out;
	}

	private static function choice_label( $key, $choice ) {
		if ( is_string( $choice ) ) {
			return wp_strip_all_tags( $choice );
		}

		if ( is_array( $choice ) ) {
			foreach ( array( 'label', 'text' ) as $k ) {
				if ( ! empty( $choice[ $k ] ) && is_string( $choice[ $k ] ) ) {
					return wp_strip_all_tags( $choice[ $k ] );
				}
			}
			foreach ( array( 'title', 'alt' ) as $k ) {
				if ( ! empty( $choice['small'][ $k ] ) && is_string( $choice['small'][ $k ] ) ) {
					return wp_strip_all_tags( $choice['small'][ $k ] );
				}
			}
		}

		return '' === (string) $key ? __( 'Default', 'fw' ) : ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) );
	}
}
