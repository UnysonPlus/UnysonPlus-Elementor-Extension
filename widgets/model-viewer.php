<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** 3D model viewer — a GLB / glTF model with camera controls, lighting, AR, hotspots and scroll choreography. */
class FW_Elementor_Widget_Model_Viewer extends FW_Elementor_Shortcode_Widget {

	public function shortcode_tag() {
		return 'model_viewer';
	}

	public function get_title() {
		return __( '3D Model', 'fw' );
	}

	protected function fallback_icon() {
		return 'eicon-cube';
	}

	public function get_categories() {
		return array( 'unysonplus-motion' );
	}

	public function get_keywords() {
		return array( '3d', 'model', 'glb', 'gltf', 'ar', 'viewer' );
	}

	protected function sections() {
		return array(
			array( 'id' => 'model', 'label' => __( 'Model', 'fw' ), 'tab' => 'content', 'options' => array( 'model_url', 'model_file', 'alt', 'poster', 'height', 'variants_show', 'variant_default', 'animation_autoplay', 'animation_name' ) ),
			array( 'id' => 'camera', 'label' => __( 'Camera', 'fw' ), 'tab' => 'content', 'options' => array( 'camera_controls', 'disable_zoom', 'disable_pan', 'auto_rotate', 'rotation_speed', 'auto_rotate_delay', 'camera_orbit', 'field_of_view', 'min_fov', 'max_fov', 'min_orbit', 'max_orbit', 'interaction_prompt' ) ),
			array( 'id' => 'lighting', 'label' => __( 'Lighting', 'fw' ), 'tab' => 'content', 'options' => array( 'environment', 'env_image', 'skybox', 'tone_mapping', 'exposure', 'shadow_intensity', 'shadow_softness' ) ),
			array( 'id' => 'ar', 'label' => __( 'AR', 'fw' ), 'tab' => 'content', 'options' => array( 'ar', 'ar_placement', 'ar_scale' ) ),
			array( 'id' => 'hotspots', 'label' => __( 'Hotspots', 'fw' ), 'tab' => 'content', 'options' => array( 'hotspots', 'hotspot_hide_backside' ) ),
			array( 'id' => 'choreo', 'label' => __( 'Scroll Choreography', 'fw' ), 'tab' => 'content', 'options' => array( 'choreo_enable', 'choreo_layer', 'choreo_size', 'choreo_travel', 'choreo_smooth', 'choreo_guide', 'choreo_keys' ) ),
			array( 'id' => 'style', 'label' => __( 'Background', 'fw' ), 'tab' => 'style', 'options' => array( 'background', 'bg_color' ) ),
		);
	}
}
