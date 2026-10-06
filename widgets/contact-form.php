<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Contact form — fields (type, label, required, placeholder, choices, width), where the
 * entries go (email, stored entries, newsletter list), the messages, and the field and
 * button styling. Submissions go through the forms extension exactly as on a page-builder
 * page: the form's settings are saved under its id when it renders.
 *
 * Not exposed: a field's validation constraints, help text and conditional logic (kept in
 * the field's residue), and the mailer settings (Unyson+ → Mailer).
 */
class FW_Elementor_Widget_Contact_Form extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'contact_form';
	}

	public function get_title() {
		return __( 'Form', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_keywords() {
		return array( 'form', 'contact', 'fields', 'enquiry', 'booking', 'signup' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'fields', 'label' => __( 'Fields', 'fw' ), 'tab' => 'content', 'options' => array( 'form' ) ),
			array( 'id' => 'actions', 'label' => __( 'After Submit', 'fw' ), 'tab' => 'content', 'options' => array( 'email_to', 'subject_message', 'action_store', 'action_subscribe', 'action_subscribe_list', 'action_subscribe_tags', 'action_subscribe_name' ) ),
			array( 'id' => 'messages', 'label' => __( 'Button & Messages', 'fw' ), 'tab' => 'content', 'options' => array( 'submit_button_text', 'success_message', 'failure_message' ) ),
			array( 'id' => 'layout', 'label' => __( 'Form Layout', 'fw' ), 'tab' => 'style', 'options' => array( 'form_max_width', 'form_align', 'margin' ) ),
			array( 'id' => 'fields_style', 'label' => __( 'Fields', 'fw' ), 'tab' => 'style', 'options' => array( 'field_radius', 'field_border_width', 'field_padding', 'field_bg', 'field_text', 'field_border', 'field_focus', 'label_color' ) ),
			array( 'id' => 'button', 'label' => __( 'Button', 'fw' ), 'tab' => 'style', 'options' => array( 'button_style', 'button_size', 'button_shape', 'button_full', 'button_align' ) ),
		);
	}

	/** Starts from the forms extension's own "Contact" starter. */
	protected function control_overrides() {
		$starters = class_exists( 'FW_Forms_Starters' ) ? FW_Forms_Starters::_filter_predefined( array() ) : array();

		// The forms extension refuses to send without a recipient ("Unable to process the
		// form"), so a new widget starts addressed to the site's admin email.
		$overrides = array( 'email_to' => array( 'default' => (string) get_option( 'admin_email' ) ) );

		if ( empty( $starters['fw-forms-contact']['json'] ) ) {
			return $overrides;
		}

		$rows = FW_Elementor_Option_Bridge::to_settings(
			array( 'form' => array( 'json' => $starters['fw-forms-contact']['json'] ) ),
			$this->leaves(),
			array( 'form' )
		);

		if ( isset( $rows['form__fields'] ) ) {
			$overrides['form'] = array( 'default' => $rows['form__fields'] );
		}

		return $overrides;
	}

	/**
	 * A stable form id. The option is `unique`, so its declared default is a fresh random id
	 * on every render — and the forms extension saves the form's settings under that id each
	 * time, which would add a row per page view and break a submission arriving after a
	 * re-render. An id carried in from page-builder atts (in the residue) is kept.
	 */
	public function build_atts( array $settings, $element_id = null ) {
		$atts       = parent::build_atts( $settings, $element_id );
		$element_id = null !== $element_id ? (string) $element_id : (string) $this->get_id();

		$residue = isset( $settings[ FW_Elementor_Option_Bridge::EXTRA ] ) ? FW_Elementor_Option_Bridge::decode_residue( $settings[ FW_Elementor_Option_Bridge::EXTRA ] ) : null;

		if ( empty( $residue['id'] ) && '' !== $element_id ) {
			$atts['id'] = 'el' . $element_id;
		}

		return $atts;
	}
}
