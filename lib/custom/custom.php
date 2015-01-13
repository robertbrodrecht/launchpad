<?php
/**
 * Your Custom Functions
 *
 * See /lib/custom/examples.php for common examples.
 * 
 * @package 	Launchpad
 * @since		1.0
 */


function custom_launchpad_custom_post_types($post_types) {
	$custom_post_types = array(
		'page' => array(
			'flexible' => array(
				'page_flexible' => array(
					'name' => 'Page Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					// Use array_merge to add on to defaults or make your own.  
					// Use launchpad_modify_default_flexible_modules filter to modify the defaults.
					'modules' => launchpad_get_default_flexible_modules()
				)
			),
/*
			'metaboxes' => array(
				'launchpad_sample_side_metabox' => array(
					'name' => 'Sample Side Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample metabox is designed to help you build your own metaboxes.</p>',
					'fields' => array(
						'checkbox_toggle' => array(
							'name' => 'Checkbox',
							'help' => '<p>This is a file input.</p>',
							'args' => array(
								'toggle' => array(
									'sample_checkbox_test_show_on' => array(
										'show_when' => 'on'
									),
									'sample_checkbox_test_hide_on' => array(
										'hide_when' => 'on'
									),
									'sample_checkbox_test_show_off' => array(
										'show_when' => ''
									),
									'sample_checkbox_test_hide_off' => array(
										'hide_when' => ''
									)
								),
								'type' => 'checkbox',
							),
						),
						'sample_checkbox_test_show_on' => array(
							'name' => 'Shown if checkbox is on.',
							'help' => '<p>This is a file input.</p>',
							'args' => array(
								'type' => 'checkbox'
							)
						),
						'sample_checkbox_test_hide_on' => array(
							'name' => 'Hidden if checkbox is on.',
							'help' => '<p>This is a file input.</p>',
							'args' => array(
								'type' => 'checkbox'
							)
						),
						'sample_checkbox_test_show_off' => array(
							'name' => 'Shown if checkbox is off.',
							'help' => '<p>This is a file input.</p>',
							'args' => array(
								'type' => 'checkbox'
							)
						),
						'sample_checkbox_test_hide_off' => array(
							'name' => 'Hidden if checkbox is off.',
							'help' => '<p>This is a file input.</p>',
							'args' => array(
								'type' => 'checkbox'
							)
						)
					)
				)
			)
*/
		),
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');
