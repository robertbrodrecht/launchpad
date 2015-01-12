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
			'metaboxes' => array(
				'launchpad_sample_side_metabox' => array(
					'name' => 'Sample Side Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample metabox is designed to help you build your own metaboxes.</p>',
					'fields' => array(
						'sample_side_metabox_checkbox_1' => array(
							'name' => 'I agree',
							'help' => '<p>This is a checkbox.</p>',
							'args' => array(
								'type' => 'select',
								'options' => array(
									1 => 1,
									2 => 2,
									3 => 3	
								),
								'toggle' => array(
									'sample_side_metabox_checkbox_2' => array(1, 2)
								)
							)
						),
						'sample_side_metabox_checkbox_2' => array(
							'name' => 'I agree again',
							'help' => '<p>This is a checkbox.</p>',
							'args' => array(
								'type' => 'checkbox'
							)
						),
					)
				)
			)
		),
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');
