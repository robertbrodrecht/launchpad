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
				'test' => array(
					'name' => 'Test',
					'fields' => array(
						'test1' => array(
							'name' => 'Test 1',
							'args' => array(
								'type' => 'text',
								'default' => 'HELLO'
							)
						),
						'test2' => array(
							'name' => 'Test 2',
							'args' => array(
								'type' => 'select',
								'options' => array(
									'H' => 'H',
									'E' => 'E',
									'L' => 'L',
									'O' => 'O'
								),
								'default' => 'E'
							)
						)
					)
				)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');
