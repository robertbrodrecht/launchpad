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

				
				'repeating' => array(
					'name' => 'Repeater',
					'args' => array(
						'type' => 'repeater',
						'label' => 'Items',
						'subfields' => array(
							'item1' => array(
								'name' => 'Title',
								'args' => array(
									'type' => 'text'
								)
							),
							'item2' => array(
								'name' => 'Sub Title',
								'args' => array(
									'type' => 'text'
								)
							),
						)
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


function custom_launchpad_setting_fields($opts) {
	$opts['testing'] = array(
		'name' => 'Testing',
		'args' => array(
			'type' => 'subfield',
			'subfields' => array(					
				'repeating' => array(
					'name' => 'Repeater',
					'args' => array(
						'type' => 'repeater',
						'label' => 'Items',
						'subfields' => array(
							'item1' => array(
								'name' => 'Title',
								'args' => array(
									'type' => 'text'
								)
							),
							'item2' => array(
								'name' => 'Sub Title',
								'args' => array(
									'type' => 'text'
								)
							),
						)
					)
				)
			)
		)
	);
	
	return $opts;
}
add_filter('launchpad_setting_fields', 'custom_launchpad_setting_fields');