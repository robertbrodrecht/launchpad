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
	$add_rewrite = array(
		'favicon.ico' => THEME_PATH . '/favicon.ico',
		'css/(.*)' => THEME_PATH . '/css/$1',
	  	'js/(.*)' => THEME_PATH . '/js/$1',
	  	'images/(.*)' => THEME_PATH . '/images/$1',
	  	'support/(.*)' => THEME_PATH . '/support/$1',
		'api/(.*)' => 'wp-admin/admin-ajax.php',
		'manifest.appcache' => 'wp-admin/admin-ajax.php?action=cache_manifest',
		'manifest.obsolete.appcache' => 'wp-admin/admin-ajax.php?action=cache_manifest_obsolete',
		'sitemap-(\d*).xml/?' => 'wp-admin/admin-ajax.php?action=sitemap&sitemap=$1',
		'sitemap-index\.xml/?' => 'wp-admin/admin-ajax.php?action=sitemap',
	);
	
	$add_rewrite = apply_filters('launchpad_rewrite_rules', $add_rewrite);
	
	$wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $add_rewrite);
	return $content;
}
add_action('generate_rewrite_rules', 'launchpad_rewrite_rules');