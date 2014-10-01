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
										'type' => 'repeater',
										'subfields' => array(
											'test' => array(
												'name' => 'test',
												'args' => array(
													'type' => 'relationship'
												)
											)
										)
									)
								),
							)
						)
					)
				)
								
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');