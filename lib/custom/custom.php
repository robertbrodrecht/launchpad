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
		// metaboxes is not required.
		'metaboxes' => array(
				'sidebar_flexible_options' => array(
					'name' => 'Sidebar Flexible Options',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>Control how sidebar flexible content works.</p>',
					'watch' => array(
						'#page_template' => array(
							'hide_when' => 'default'
						)
					),
					'fields' => array(
						'sidebar_coerce' => array(
							'name' => 'Move Desktop Sidebar To',
							'help' => '<p>The template selection controls the source order of the sidebar.  The source order dictates whether the sidebar is first or last in the mobile view.  At desktop sizes, however, the sidebar can be on the right or left, regardless of the source order.  The default is to allow the sidebar to appear naturally (left side if first and right side if last).</p>',
							'args' => array(
								'type' => 'select',
								'options' => array(
									'auto' => 'Auto',
									'left' => 'Left',
									'right' => 'Right',
								),
								'default' => 'auto'
							)
						),
						'sidebar_widget_location' => array(
							'name' => 'Widget Location',
							'help' => '<p>Decide where the widget area should appear.</p>',
							'args' => array(
								'type' => 'select',
								'options' => array(
									'below' => 'Below Flexible Content',
									'above' => 'Above Flexible Content',
								),
								'default' => 'below'
							)
						),
						'sidebar_flexible_inherit_from_parent' => array(
							'name' => 'Inherit Sidebar',
							'help' => '<p>If checked, this page will inherit the closest ancestral sidebar.</p>',
							'args' => array(
								'type' => 'checkbox',
								'default' => 'on',
								'watch' => array(
									'#parent_id' => array(
										'hide_when' => ''
									)
								)
							)
						),
								'sample_side_metabox_repeater' => array(
									'name' => 'Repeater',
									'help' => '<p>This is a repeater.</p>',
									'args' => array(
										'type' => 'repeater',
										'label' => 'HUH',
										'subfields' => array(
											'sample_side_metabox_repeater_a' => array(
												'name' => 'Test Repeater A',
												'args' => array(
													'type' => 'wysiwyg'
												)
											),
											'sample_side_metabox_repeater_b' => array(
												'name' => 'Test Repeater B',
												'args' => array(
													'type' => 'wysiwyg'
												)
											)
										)
									)
								),
					)
				)
			),
			'flexible' => array(
				'page_flexible' => array(
					'name' => 'Page Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'display' => 'main',
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					// Use array_merge to add on to defaults or make your own.  
					// Use launchpad_modify_default_flexible_modules filter to modify the defaults.
					'modules' => launchpad_get_default_flexible_modules()
				),
				'sidebar_flexible' => array(
					'name' => 'Sidebar Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'display' => 'sidebar',
					'watch' => array(
						'#page_template' => array(
							'hide_when' => 'default'
						)
					),
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					// Use array_merge to add on to defaults or make your own.  
					// Use launchpad_modify_default_flexible_modules filter to modify the defaults.
					'modules' => launchpad_get_default_flexible_modules()
				)
			),
		),
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');
