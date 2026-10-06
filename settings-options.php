<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'panel_box' => [
		'title'   => __( 'Elementor Widget Panel', 'fw' ),
		'type'    => 'box',
		'options' => [
			'group_panel' => [
				'type'    => 'group',
				'options' => [
					'hide_pro_promos' => [
						'label'        => __( 'Hide locked Pro widgets', 'fw' ),
						'desc'         => __( 'Elementor lists the widgets of its paid version as locked tiles. The Unyson+ category offers working equivalents for most of them, so the locked tiles are hidden. Has no effect when Elementor Pro is installed.', 'fw' ),
						'type'         => 'switch',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no', 'label' => __( 'No', 'fw' ) ],
						'value'        => 'yes',
					],
				],
			],
		],
	],
];
