<?php

/**
 * API
 *
 * Handles the AJAX API calls for the basic functionality of the front end.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */


/**
 * Sample API Call
 *
 * @since   	Version 1.0
 */
function launchpad_hello_world() {
	echo json_encode('Hello, World!');
	exit;
}
add_action('wp_ajax_hello_world', 'launchpad_hello_world');
add_action('wp_ajax_nopriv_hello_world', 'launchpad_hello_world');


/**
 * Simple Test for User Logged In
 *
 * @since   	Version 1.0
 */
function launchpad_user_logged_in() {
	header('Content-type: application/json');
	echo json_encode(is_user_logged_in());
	exit;
}
add_action('wp_ajax_user_logged_in', 'launchpad_user_logged_in');
add_action('wp_ajax_nopriv_user_logged_in', 'launchpad_user_logged_in');

/**
 * Generate an App Cache Manifest
 *
 * @since   	Version 1.0
 */
function launchpad_cache_manifest() {
	$site_options = get_option('launchpad_site_options', '');

	$file_list = array();
	$latest = filemtime(__FILE__);
	
	// Locations for images and CSS.
	$paths = array(
			'/' . THEME_PATH . '/css/' => '/css/',
			'/' . THEME_PATH . '/js/' => '/js/',
			'/' . THEME_PATH . '/images/' => '/images/'
		);
	
	// Load all the images and CSS.
	foreach($paths as $path => $rewrite_path) {
		$path_local = $_SERVER['DOCUMENT_ROOT'] . $path;
		$files = launchpad_scandir_deep($path_local);
		if($files) {
			foreach($files as $file) {
				if(substr($file, 0, 1) !== '.' && filesize($path_local . $file) <= 512000) {
					$file_list[] = $rewrite_path . $file;
					if(filemtime($path_local . $file) > $latest) {
						$latest = filemtime($path_local . $file);
					}
				}
			}
		}
	}
	
	// Get the list of custom post types.
	$post_types = get_post_types(
			array(
				'public' => true,
				'publicly_queryable' => true,
				'_builtin' => false
			)
		);
		
	$post_types = array_values($post_types);
	$post_types[] = 'page';
	
	// Query for all custom post types and pages.
	$q = new WP_Query(
			array(
				'post_type' => $post_types,
				'posts_per_page' => -1
			)
		);
	foreach($q->posts as $p) {
		$pl = get_permalink($p->ID);
		$file_list[] = $pl;
		//$file_list[] = $pl . (stristr($pl, '?') !== false ? '&' : '?') . 'launchpad_ajax=true';
		
		// Get the assets out of the page.
		$output = file_get_contents($pl);
		preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
		if($matches[1]) {
			foreach($matches[1] as $asset_path) {
				if(!in_array($asset_path, $file_list)) {
					$file_list[] = $asset_path;
				}
			}
		}
		
		$pl = explode('/', $pl);
		array_pop($pl);
		while(count($pl) > 3) {
			array_pop($pl);
			$tmp_pl = implode('/', $pl) . '/';
			if(!in_array($tmp_pl, $file_list)) {
				$file_list[] = $tmp_pl;
				//$file_list[] = $tmp_pl . (stristr($tmp_pl, '?') !== false ? '&' : '?') . 'launchpad_ajax=true';
			}
		}
		if(strtotime($p->post_modified) > $latest) {
			$latest = strtotime($p->post_date);
		}
	}
	
	// Get 100 posts.
	$q = new WP_Query(
			array(
				'post_type' => 'post',
				'posts_per_page' => 100
			)
		);
	foreach($q->posts as $p) {
		$pl = get_permalink($p->ID);
		$file_list[] = $pl;
		//$file_list[] = $pl . (stristr($pl, '?') !== false ? '&' : '?') . 'launchpad_ajax=true';
		
		// Get the assets out of the post.
		$output = file_get_contents($pl);
		preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
		if($matches[1]) {
			foreach($matches[1] as $asset_path) {
				if(!in_array($asset_path, $file_list)) {
					$file_list[] = $asset_path;
				}
			}
		}
		
		$pl = explode('/', $pl);
		array_pop($pl);
		while(count($pl) > 3) {
			array_pop($pl);
			$tmp_pl = implode('/', $pl) . '/';
			if(!in_array($tmp_pl, $file_list)) {
				$file_list[] = $tmp_pl;
				//$file_list[] = $tmp_pl . (stristr($tmp_pl, '?') !== false ? '&' : '?') . 'launchpad_ajax=true';
			}
		}
		if(strtotime($p->post_modified) > $latest) {
			$latest = strtotime($p->post_date);
		}
	}
	
	// Get enough archive pages to support the posts we got.
	$posts_page = get_option('page_for_posts');
	if($posts_page) {
		$posts_page = get_permalink($posts_page);
		$file_list[] = $posts_page;
		$total_pages = ceil($q->found_posts/get_option('posts_per_page'));
		for($i = 1; $i < $total_pages; $i++) {
			$tmp_pl = $posts_page . 'page/' . $i . '/';
			$file_list[] = $tmp_pl;
			//$file_list[] = $tmp_pl . (stristr($tmp_pl, '?') !== false ? '&' : '?') . 'launchpad_ajax=true';
		}
	}
	
	sort($file_list);
	
	//header('Content-type: text/plain'); // Use this for debugging.
	header('Content-type: text/cache-manifest');
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime('+1 second')));
	
	echo "CACHE MANIFEST\n\n";
	echo "# Last Modified: " . date('Y-m-d H:i:s T', $latest) . " \n\n";
	echo "CACHE:\n";
	echo implode("\n", array_unique($file_list));
	echo "\n\n";
	
	echo "NETWORK:\n*\n\n";
	echo "FALLBACK:\n/ /support/offline.html\n\n";
	exit;
}
add_action('wp_ajax_cache_manifest', 'launchpad_cache_manifest');
add_action('wp_ajax_nopriv_cache_manifest', 'launchpad_cache_manifest');


/**
 * Obsolete an App Cache Manifest
 *
 * @since   	Version 1.0
 */
function launchpad_cache_manifest_obsolete() {
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime('-1 month')));
	http_response_code(404);
	exit;
}
add_action('wp_ajax_cache_manifest_obsolete', 'launchpad_cache_manifest_obsolete');
add_action('wp_ajax_nopriv_cache_manifest_obsolete', 'launchpad_cache_manifest_obsolete');


/**
 * Generate Icon Images
 *
 * @since   	Version 1.0
 */
function launchpad_generate_icon() {
	$site_options = get_option('launchpad_site_options', '');
	$icon = $site_options['icon'];
	if(!$_GET['type'] || !$icon || !file_exists($icon)) {
		http_response_code(404);
		exit;
	}
	switch($_GET['type']) {
		case 'favicon.ico':
			
		break;
	}
	exit;
}
add_action('wp_ajax_cache_generate_icon', 'launchpad_generate_icon');
add_action('wp_ajax_nopriv_generate_icon', 'launchpad_generate_icon');