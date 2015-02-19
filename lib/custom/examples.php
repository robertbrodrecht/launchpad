<?php
/**
 * Your Custom Functions
 *
 * Custom filters:
 * 
 * * launchpad_sidebar_content: A filter to modify sidebar content.
 * * launchpad_determine_best_template_file: A filter to modify the template used for a certain page.
 * * launchpad_post_content_string: A filter to control the content of the article if you don't want to make a template file.
 * * launchpad_post_header_string: A filter to control the <header> of the article if you don't want to make a template file.
 * * launchpad_modify_default_flexible_modules: Allows changing default flexible content modules.
 * * launchpad_image_sizes: array of image sizes.  See example.
 * * launchpad_post_types: array of post types.  See example.
 * * launchpad_mce_style_formats: array of MCE formats.  See example.
 * * launchpad_body_class: array of classes.
 * * launchpad_cache_manifest_file_paths: array of default manifest paths.
 * * launchpad_post_formats: array of post formats.
 * * launchpad_nav_menus: array of nav menus.
 * * launchpad_title: string title.
 * * launchpad_excerpt: string excerpt.
 * * launchpad_setting_fields: array of theme settings fields.
 * * launchpad_cache_file_path: string $cache path, int/bool $post_id, int/bool $type.
 * * launchpad_activate_home_name: string Name of home page. Only fires on activation.
 * * launchpad_activate_articles_name: string Name of articles page. Only fires on activation.
 * * launchpad_activate_articles_path: string Articles path Only fires on activation.
 * * launchpad_activate_upload_path: string Upload path. Only fires on activation.  See example.
 * 
 * @package 	Launchpad
 * @since		1.0
 */


// Change to true to use /img/ instead of /images/
$use_img = false;
// Change to true to use /assets/ instead of /uploads/
$use_uploads = false;

/**
 * Manifest for /img/
 * 
 * @param		array $paths Path manifest paths to replace.
 * @since		1.0
 */
function custom_launchpad_cache_manifest_file_paths($paths) {
	unset($paths[CHILD_THEME_PATH . '/images/']);
	$paths[CHILD_THEME_PATH . '/img/'] = '/img/';
		
	return $paths;
}


/**
 * Manifest for /img/
 * 
 * @param		array $rewrites Rewrites to replace.
 * @since		1.0
 */
function custom_launchpad_rewrite_rules($rewrites) {
	unset($rewrites['images/(.*)']);
	$rewrites['img/(.*)'] = CHILD_THEME_PATH . '/img/$1';
	return $rewrites;
}

if($use_img) {
	add_filter('launchpad_cache_manifest_file_paths', 'custom_launchpad_cache_manifest_file_paths');
	add_filter('launchpad_rewrite_rules', 'custom_launchpad_rewrite_rules');
}


/**
 * Custom Launchpad Options Page
 * 
 * By default, the options are under Settings > Launchpad.  Change anything below to customize it.
 * 
 * @param		array $opts Settings to get the menu top-level.
 * @since		1.0
 */
function custom_launchpad_theme_options_page($opts) {
	// The slug for the parent page, if any.  See first URL @link above.
	$opts['launchpad_options']['parent_page'] = null;
	// Name at the top of the page.
	$opts['launchpad_options']['page_name'] = 'Theme Options';
	// Name on the nav sidebar menu.
	$opts['launchpad_options']['menu_name'] = 'Special';
	// The menu icon.  See second URL @link above.
	$opts['launchpad_options']['menu_icon'] = 'dashicons-yes';
	// The menu position.
	$opts['launchpad_options']['menu_position'] = 999;
	
	// IF YOU WANTED TO ADD A SECTION:
	$opts['launchpad_options']['sections']['my_new_section'] = array(
		'section_name' => 'Custom Section',
		'description' => 'A special note for the user can go here.',
		'fields' => array(
			'my_new_field' => array(
				'name' => 'A Sample Field',
				'args' => array(
					'type' => 'text'
				)
			),
		)
	);
	
	return $opts;
}
add_filter('launchpad_setting_fields', 'custom_launchpad_theme_options_page');


/**
 * Set Upload Path to /uploads/ 
 * 
 * @param		array $rewrites Rewrites to replace.
 * @since		1.0
 */
function custom_launchpad_activate_upload_path($str) {
	// This should really be 'uploads' but the code will fix the additional slashes.
	return '/uploads/';
}
if($use_uploads) {
	add_filter('launchpad_activate_upload_path', 'custom_launchpad_activate_upload_path');
}


/**
 * Custom Image Sizes
 *
 * These will be added via add_image_size. Use an array of arrays matching add_image_size's parameters:
 * 
 * array(name, width, height, crop)
 * 
 * @param		array $images The original images array.
 * @since		1.0
 */
function custom_launchpad_image_sizes($images) {
	$images[] = array('XXL', '1600', '1600', false);
	return $images;
}
add_filter('launchpad_image_sizes', 'custom_launchpad_image_sizes');


/**
 * Custom Post Types
 * 
 * Use the format below to register custom post types.  You can use a simplified format or
 * include a "label" key to completely specify your own configuration as though you were
 * writing your own with register_post_type().  Taxonomies are optional.
 * 
 * @param		array $post_types The original post types array.
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
				)
		),
		'launchpad_sample' => array(
				'plural' => 'Samples',
				'single' => 'Sample',
				'slug' => 'samples',
				'help' => '<p>This is a sample post type designed to help you see how things work.</p>',
				'menu_position' => null,
				// hierarchical is not required.  default: false.
				'hierarchical' => true,
				// supports is not required.  default: title, editor, thumbnail.
				'supports' => array(
						'title',
						'editor',
						'thumbnail',
						'page-attributes'
					),
				// taxonomies is not required.
				'taxonomies' => array(
						'launchpad_sample_tax' => array(
								'plural' => 'Sample Taxonomies',
								'single' => 'Sample Taxonomy',
								'slug' => 'sample_taxonomy'
							)
					),
				// metaboxes is not required.
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
										'type' => 'checkbox'
									)
								),
								'sample_side_metabox_file' => array(
									'name' => 'Upload a file',
									'help' => '<p>This is a file input.</p>',
									'args' => array(
										'type' => 'file'
									)
								),
								'sample_side_metabox_value_text' => array(
									'name' => 'Text',
									'help' => '<p>This field contains sample text.</p>',
									'args' => array(
										'type' => 'text'
									)
								),
								'sample_side_metabox_value_textarea' => array(
									'name' => 'Textarea',
									'help' => '<p>This field contains sample textarea.</p>',
									'args' => array(
										'type' => 'textarea'
									)
								),
								'sample_side_metabox_value_select' => array(
									'name' => 'Select',
									'help' => '<p>This field allows you to select one of three options.</p>',
									'args' => array(
										'type' => 'select',
										'options' => array(
											'A' => 'A',
											'B' => 'B',
											'C' => 'C'
										)
									)
								),
								'sample_side_metabox_value_selectmulti' => array(
									'name' => 'Select Multiple',
									'help' => '<p>This field allows you to select any of three options.</p>',
									'args' => array(
										'type' => 'selectmulti',
										'options' => array(
											'A' => 'A',
											'B' => 'B',
											'C' => 'C'
										)
									)
								),
								'sample_side_metabox_wysiwyg' => array(
									'name' => 'WYSIWYG',
									'help' => '<p>This is a WYSIWYG input.</p>',
									'args' => array(
										'type' => 'wysiwyg'
									)
								),
								'sample_side_metabox_menu' => array(
									'name' => 'Select a Menu',
									'help' => '<p>This is a menu input.</p>',
									'args' => array(
										'type' => 'menu'
									)
								),
								'sample_side_metabox_relationship' => array(
									'name' => 'Relationship',
									'help' => '<p>This is a relationship.</p>',
									'args' => array(
										'type' => 'relationship',
										'post_type' => 'page',
										'limit' => 2
									)
								),
								'sample_side_metabox_taxonomy' => array(
									'name' => 'Taxonomy',
									'help' => '<p>This is a taxonomy.</p>',
									'args' => array(
										'type' => 'taxonomy',
										'taxonomy' => 'launchpad_sample_tax',
										'multiple' => true
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
													'type' => 'text'
												)
											),
											'sample_side_metabox_repeater_b' => array(
												'name' => 'Test Repeater B',
												'args' => array(
													'type' => 'textarea'
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
							'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
							// Use array_merge to add on to defaults or make your own.  
							// Use launchpad_modify_default_flexible_modules filter to modify the defaults.
							'modules' => launchpad_get_default_flexible_modules()
						)
					)
			)
	);
	
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');

/**
 * Custom Editor Styles
 * 
 * MAKE SURE THESE ARE REPRESENTED IN _objects.scss!!!
 * 
 * @param		array $formats The original MCE formats array.
 * @since		1.0
 */
function custom_launchpad_mce_style_formats($formats) {
	$formats[] = array(
		'title' => 'Callout',  
		'block' => 'div',  
		'classes' => 'callout',
		'wrapper' => true
	);
	return $formats;
}
//add_filter('launchpad_mce_style_formats', 'custom_launchpad_mce_style_formats');


/**
 * Custom Editor Buttons
 * 
 * You can add more buttons such as 'sup' and 'fontselect.'
 * 
 * @param		array $buttons The existing buttons.
 * @since		1.0
 * @link		List of Buttons http://www.tinymce.com/wiki.php/TinyMCE3x:Buttons/controls
 */
function enable_more_buttons($buttons) {
	$buttons[] = 'hr';
	
	return $buttons;
}
//add_filter("mce_buttons", "enable_more_buttons");
