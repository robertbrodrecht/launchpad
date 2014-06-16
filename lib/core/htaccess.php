<?php
/**
 * .htaccess Related Functions
 *
 * Anything to do with editing the .htaccess file goes here.
 *
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Create rewrite rules
 *
 * Assigns short short absolute URL paths to make theming easier.
 * This is modified from the Roots theme.
 *
 * @param		string $content
 * @since		1.0
 */
function launchpad_rewrite_rules($content) {
	global $wp_rewrite;
	
	// WP Rewrites need the '/' removed.
	$tmp_theme = substr(THEME_PATH, 1);
	
	// Set the rules we need.
	$add_rewrite = array(
		'favicon.ico' => $tmp_theme . '/favicon.ico',
		'css/(.*)' => $tmp_theme . '/css/$1',
	  	'js/(.*)' => $tmp_theme . '/js/$1',
	  	'images/(.*)' => $tmp_theme . '/images/$1',
	  	'support/(.*)' => $tmp_theme . '/support/$1',
		'api/(.*)' => 'wp-admin/admin-ajax.php',
		'download/(.*)' => 'wp-admin/admin-ajax.php?action=download&file=$1',
		'manifest.appcache' => 'wp-admin/admin-ajax.php?action=cache_manifest',
		'sitemap-(\d*).xml/?' => 'wp-admin/admin-ajax.php?action=sitemap&sitemap=$1',
		'sitemap-index\.xml/?' => 'wp-admin/admin-ajax.php?action=sitemap',
	);
	
	// Apply filters so the developer can change them.
	$add_rewrite = apply_filters('launchpad_rewrite_rules', $add_rewrite);
	
	// Apply the rules.
	$wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $add_rewrite);
	return $content;
}
if(is_admin()) {
	add_action('generate_rewrite_rules', 'launchpad_rewrite_rules');
}


/**
 * Include HTML5 Boilerplate in HTACCESS
 * 
 * This is modified from the Roots theme.
 *
 * @param		string $content
 * @since		1.0
 */
function launchpad_add_h5bp_htaccess($content) {
	global $wp_rewrite, $site_options;
	
	// Check to see if the function exists.
	// For some reason it doesn't on theme activation.
	if(!function_exists('extract_from_markers')) {
		return $content;
	}
	
	// Get the path to the htaccess file.
	$home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
	$htaccess_file = $home_path . '.htaccess';
	
	// If we can edit the htaccess file in one way or another...
	if(
		(!file_exists($htaccess_file) && is_writable($home_path)) || 
		is_writable($htaccess_file)
	) {
		// Extract the boilerplate.
		$h5bp_rules = extract_from_markers($htaccess_file, 'HTML5 Boilerplate');
		
		// If there are no Boilerplate Rules and the user wants them, add them.
		if ($h5bp_rules === array() && $site_options['html5_bp'] === true) {
			$filename = '..' . THEME_PATH . '/support/H5BPv4.3_htaccess';
			$boilerplate_rules = extract_from_markers($filename, 'HTML5 Boilerplate');
		
		// If there are Boilerplate rules and the user doesn't want them, remove them.
		} else if($h5bp_rules !== array() && $site_options['html5_bp'] !== true) {
			$boilerplate_rules = '';
		}
		
		// Update the HTACCESS file.
		insert_with_markers($htaccess_file, 'HTML5 Boilerplate', $boilerplate_rules);
	}
	
	return $content;
}
if(is_admin()) {
	add_action('generate_rewrite_rules', 'launchpad_add_h5bp_htaccess');
}