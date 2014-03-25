<?php
/**
 * Your Custom Functions
 *
 * Custom filters:
 * 
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
$use_assets = false;

/**
 * Manifest for /img/
 * 
 * @param		array $paths Path manifest paths to replace.
 * @since		1.0
 */
function custom_launchpad_cache_manifest_file_paths($paths) {
	unset($paths['/' . THEME_PATH . '/images/']);
	$paths['/' . THEME_PATH . '/img/'] = '/img/';
		
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
	$rewrites['img/(.*)'] = THEME_PATH . '/img/$1';
	return $rewrites;
}

if($use_img) {
	add_filter('launchpad_cache_manifest_file_paths', 'custom_launchpad_cache_manifest_file_paths');
	add_filter('launchpad_rewrite_rules', 'custom_launchpad_rewrite_rules');
}


/**
 * Set Upload Path to /assets/ 
 * 
 * @param		array $rewrites Rewrites to replace.
 * @since		1.0
 */
function custom_launchpad_activate_upload_path($str) {
	// This should really be 'assets' but the code will fix the additional slashes.
	return '/assets/';
}
if($use_assets) {
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
		'launchpad_sample' => array(
				'plural' => 'Samples',
				'single' => 'Sample',
				'slug' => 'samples',
				'menu_position' => null,
				'taxonomies' => array(
						'launchpad_sample_tax' => array(
								'plural' => 'Sample Taxonomies',
								'single' => 'Sample Taxonomy',
								'slug' => 'sample_taxonomy'
							)
					),
				'metaboxes' => array(
						'launchpad_sample_side_metabox' => array(
							'name' => 'Sample Side Metabox',
							'location' => 'side',
							'position' => 'default',
							'fields' => array(
								'sample_side_metabox_value_1' => array(
									'name' => 'Some Value',
									'args' => array(
										'type' => 'text',
										'default' => 'Hello'
									)
								),
								'sample_side_metabox_value_2' => array(
									'name' => 'Some Other Value',
									'args' => array(
										'type' => 'select',
										'options' => array(
											'A' => 'A',
											'B' => 'B',
											'C' => 'C'
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
add_filter('launchpad_mce_style_formats', 'custom_launchpad_mce_style_formats');