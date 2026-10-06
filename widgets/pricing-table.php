<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Pricing table — plans, monthly / yearly billing toggle, layouts, featured-plan emphasis, presets. */
class FW_Elementor_Widget_Pricing_Table extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'pricing_table';
	}

	public function get_title() {
		return __( 'Pricing Table', 'fw' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_keywords() {
		return array( 'pricing', 'price', 'plans', 'table', 'subscription' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'plans', 'label' => __( 'Plans', 'fw' ), 'tab' => 'content', 'options' => array( 'plans' ) ),
			array( 'id' => 'billing', 'label' => __( 'Billing Toggle', 'fw' ), 'tab' => 'content', 'options' => array( 'billing_toggle', 'billing_default', 'billing_monthly_label', 'billing_yearly_label', 'billing_note' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'design', 'design_settings', 'columns', 'gap', 'featured_style', 'align', 'feature_dividers', 'product_schema' ) ),
			array( 'id' => 'presets', 'label' => __( 'Presets', 'fw' ), 'tab' => 'style', 'options' => array( 'box_style', 'button_preset', 'icon_badge_preset' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'accent_color', 'bg_color', 'card_bg', 'title_color', 'price_color', 'text_color' ) ),
		);
	}

	protected function control_overrides() {
		$plan = function ( $title, $monthly, $yearly, $features, $featured = 'no' ) {
			return array(
				'plan_title'     => $title,
				'currency'       => '$',
				'price__monthly' => $monthly,
				'price__yearly'  => $yearly,
				'period__monthly' => __( '/mo', 'fw' ),
				'period__yearly'  => __( '/yr', 'fw' ),
				'features'       => $features,
				'featured'       => $featured,
				'button_label'   => __( 'Choose Plan', 'fw' ),
			);
		};

		return array(
			'plans' => array(
				'title_field' => '{{{ plan_title || "Plan" }}}',
				'default'     => array(
					$plan( __( 'Starter', 'fw' ), '9', '90', "1 Project\n5 GB Storage\nEmail Support" ),
					$plan( __( 'Pro', 'fw' ), '29', '290', "10 Projects\n50 GB Storage\nPriority Support", 'yes' ),
					$plan( __( 'Team', 'fw' ), '79', '790', "Unlimited Projects\n500 GB Storage\nPhone Support" ),
				),
			),
		);
	}
}
