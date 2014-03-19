<?php
/**
 * Functions Includes
 *
 * Include the hard work of the theme.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */

global $site_options, $post_types, $image_sizes, $launchpad_mce_style_formats;

$site_options = get_option('launchpad_site_options', '');

/**
 * Custom Post Types
 * 
 * Use the format below to register custom post types.  You can use a simplified format or
 * include a "label" key to completely specify your own configuration as though you were
 * writing your own with register_post_type().  Taxonomies are optional.
 */
$post_types = array(
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
				)
		)
);

/**
 * Custom Image Sizes
 *
 * These will be added via add_image_size. Use an array of arrays matching add_image_size's parameters:
 * 
 * array(name, width, height, crop)
 */
$image_sizes = array(
	array('XL', '1200', '1200', false),
	array('L', '1000', '1000', false),
	array('M', '800', '800', false),
	array('S', '600', '600', false),
	array('XS', '400', '400', false)
);


/**
 * Custom Editor Styles
 * 
 * MAKE SURE THESE ARE REPRESENTED IN _objects.scss!!!
 */
$launchpad_mce_style_formats = array(  
	array(  
		'title' => 'Button',
		'classes' => 'button',
		'wrapper' => false,
		'selector' => 'a'
	),
	array(  
		'title' => 'Callout',  
		'block' => 'div',  
		'classes' => 'callout',
		'wrapper' => true
	),
	array(  
		'title' => 'Crossfade Rotator',  
		'block' => 'div',  
		'classes' => 'skate',
		'wrapper' => true,
		'attributes' => (object) array('data-skate' => 'crossfade')
	)
);

/** System functions like theme activation, rewrites, etc. */
include 'functions/system.php';
/** Modifications to the admin area like options pages and admin cleanup. */
include 'functions/admin.php';
/** Security related features like limit login attempts. */
include 'functions/security.php';
/** Post Type related code for registering and creating metaboxes.  */
include 'functions/post-types.php';
/** Code for custom API calls.  */
include 'functions/api.php';
/** Template related modifications such as nav menu registration, header cleanup, page cache, etc.  */
include 'functions/template.php';
/** Custom functions for handling various duties.  */
include 'functions/utilities.php';

/** Set a couple of definitions. */
function launchpad_set_page_defines() {
	global $site_options;
	
	/** Google Analytics ID from Appearance > Theme Settings */
	define('GA_ID', $site_options['google_analytics_id']);

	/** Whether to use cache from Appearance > Theme Settings. If things get weird, turn it off by setting the value to 0. */
	define('USE_CACHE', (int) $site_options['cache_timeout']);
}
add_action('template_redirect', 'launchpad_set_page_defines');