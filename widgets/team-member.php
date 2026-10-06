<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Team member — photo, name, role, bio; Card Rows, box and image presets, colours. */
class FW_Elementor_Widget_Team_Member extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'team_member';
	}

	public function get_title() {
		return __( 'Team Member', 'fw' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_keywords() {
		return array( 'team', 'member', 'person', 'staff', 'profile' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'member', 'label' => __( 'Member', 'fw' ), 'tab' => 'content', 'options' => array( 'image', 'name', 'job', 'desc' ) ),
			array( 'id' => 'card', 'label' => __( 'Card', 'fw' ), 'tab' => 'style', 'options' => array( 'card_rows', 'box_style', 'image_style' ) ),
			array( 'id' => 'colors', 'label' => __( 'Text & Colors', 'fw' ), 'tab' => 'style', 'options' => array( 'font_size_preset', 'text_color', 'bg_color' ) ),
		);
	}

	protected function control_overrides() {
		return array(
			'image' => array( 'default' => $this->placeholder_image() ),
			'name'  => array( 'default' => __( 'Alex Rivera', 'fw' ) ),
			'job'   => array( 'default' => __( 'Product Lead', 'fw' ) ),
			'desc'  => array( 'default' => __( 'A sentence about what they do and why people like working with them.', 'fw' ) ),
		);
	}
}
