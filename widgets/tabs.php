<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Tabs — panes with optional image / badge / icon, every tab style, behaviour and motion. */
class FW_Elementor_Widget_Tabs extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'tabs';
	}

	public function get_title() {
		return __( 'Tabs', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-tabs';
	}

	public function get_keywords() {
		return array( 'tabs', 'tabbed', 'panes', 'switcher' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'tabs', 'label' => __( 'Tabs', 'fw' ), 'tab' => 'content', 'options' => array( 'tabs' ) ),
			array( 'id' => 'layout', 'label' => __( 'Layout', 'fw' ), 'tab' => 'content', 'options' => array( 'layout', 'media_side', 'orientation', 'tab_width', 'alignment', 'content_frame' ) ),
			array( 'id' => 'behaviour', 'label' => __( 'Behaviour', 'fw' ), 'tab' => 'content', 'options' => array( 'activate_on', 'activation', 'mobile', 'deep_link', 'remember', 'autoplay', 'autoplay_interval', 'fade' ) ),
			array( 'id' => 'design', 'label' => __( 'Tab Style', 'fw' ), 'tab' => 'style', 'options' => array( 'design' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'bg_color', 'tab_title_color', 'tab_content_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'tabs' => array(
				'title_field' => '{{{ tab_title || "Tab" }}}',
				'default'     => array(
					array( 'tab_title' => __( 'Overview', 'fw' ), 'tab_content' => __( 'What it is and who it is for, in a sentence or two.', 'fw' ) ),
					array( 'tab_title' => __( 'Features', 'fw' ), 'tab_content' => __( 'The three or four things it does best.', 'fw' ) ),
					array( 'tab_title' => __( 'Pricing', 'fw' ), 'tab_content' => __( 'How much it costs and what is included.', 'fw' ) ),
				),
			),
		);
	}
}
