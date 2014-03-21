<?php

/**
 * WordPress System Functions
 *
 * Functions that modify / augment the lower-level system (e.g. htaccess, image sizes, menus, and the like).
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */

$get_theme_name = explode('/themes/', get_template_directory());

/** The base WP directory. */
define('WP_BASE', wp_base_dir());
/** The theme name. */
define('THEME_NAME', next($get_theme_name));
/** Root-relative content path. */
define('RELATIVE_CONTENT_PATH', str_replace(site_url() . '/', '', content_url()));
/** Root-relative theme path. */
define('THEME_PATH', RELATIVE_CONTENT_PATH . '/themes/' . THEME_NAME);

/**
 * Get Base URL for WP Subdir
 * 
 * This is modified from the Roots theme.
 *
 * @since   	Version 1.0
 */
function wp_base_dir() {
	preg_match('!(https?://[^/|"]+)([^"]+)?!', site_url(), $matches);
	if (count($matches) === 3) {
		return end($matches);
	} else {
		return '';
	}
}

/**
 * Set up the pages and posts required for this theme
 *
 * * Updates the upload path options to /uploads/
 * * Create home page, articles page, and user pages
 * * Assign home URL and posts URL
 *
 * @since   	Version 1.0
 */
function launchpad_theme_activation_action() {
		
	$page = new WP_Query('name=home&post_type=page');
	if($page->post_count > 0) {
		$home_page_id = $page->post->ID;
	} else {
		$home_page_id = wp_insert_post(
			array(
				'post_type' => 'page',
				'post_author' => 1,
				'post_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
				'post_name' => 'home',
				'post_title' => 'Home',
				'post_status' => 'publish'
			), false
		);
	}

	$page = new WP_Query('name=articles&post_type=page');
	if($page->post_count > 0) {
		$articles_page_id = $page->post->ID;
	} else {
		$articles_page_id = wp_insert_post(
			array(
				'post_type' => 'page',
				'post_author' => 1,
				'post_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
				'post_name' => 'articles',
				'post_title' => 'Articles',
				'post_status' => 'publish'
			), false
		);
	}
	
	if($home_page_id > 0 && $articles_page_id > 0) {
		update_option('show_on_front', 'page');
		update_option('page_for_posts', $articles_page_id);
		update_option('page_on_front', $home_page_id);
	}
	
	update_option('permalink_structure', '/articles/%postname%/');
	update_option('upload_path', 'uploads');
	
	//thumbnail_size_w = ?
	//thumbnail_size_h = ?
	//thumbnail_crop = 1
	//medium_size_w = ?
	//medium_size_h = ?
	//large_size_w = ?
	//large_size_h = ?
}
add_action('after_switch_theme', 'launchpad_theme_activation_action');


/**
 * Handle Settings Redirect After Theme Switch
 *
 * @since   	Version 1.0
 */
function launchpad_settings_redirect() {
	global $pagenow;
	if (is_admin() && isset($_GET['activated']) && $pagenow == 'themes.php') {
		header('Location: themes.php?page=launchpad_settings');
		exit;
	}
}
add_action('after_switch_theme', 'launchpad_settings_redirect', 9999);


/**
 * Create rewrite rules
 *
 * Assigns short short absolute URL paths to make theming easier.
 * This is modified from the Roots theme.
 *
 * @param		string $content
 * @since   	Version 1.0
 */
function launchpad_rewrite_rules($content) {
	global $wp_rewrite;
	$add_rewrite = array(
		'css/(.*)' => THEME_PATH . '/css/$1',
	  	'js/(.*)' => THEME_PATH . '/js/$1',
	  	'images/(.*)' => THEME_PATH . '/images/$1',
	  	'img/(.*)' => THEME_PATH . '/images/$1',
	  	'support/(.*)' => THEME_PATH . '/support/$1',
		'api/(.*)' => 'wp-admin/admin-ajax.php',
		'manifest.appcache' => 'wp-admin/admin-ajax.php?action=cache_manifest',
		'manifest.obsolete.appcache' => 'wp-admin/admin-ajax.php?action=cache_manifest_obsolete',
		'favicon.ico' => THEME_PATH . '/favicon.ico'
	);
	$wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $add_rewrite);
	return $content;
}
add_action('generate_rewrite_rules', 'launchpad_rewrite_rules');


/**
 * Add Specific Headers
 *
 * @since   	Version 1.0
 */
function launchpad_http_headers() {
	header('X-UA-Compatible: IE=edge,chrome=1');
	if(isset($_GET['launchpad_ajax'])) {
		header('HTTP/1.0 200 OK', true, 200);
	}
}
add_action('send_headers', 'launchpad_http_headers');

/**
 * Include HTML5 Boilerplate in HTACCESS
 * 
 * This is modified from the Roots theme.
 *
 * @param		string $content
 * @since   	Version 1.0
 */
function launchpad_add_h5bp_htaccess($content) {
	global $wp_rewrite, $site_options;
	
	$home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
	$htaccess_file = $home_path . '.htaccess';
	$mod_rewrite_enabled = function_exists('got_mod_rewrite') ? got_mod_rewrite() : false;

	if(
		(
			!file_exists($htaccess_file) && 
			is_writable($home_path) && 
			$wp_rewrite->using_mod_rewrite_permalinks()
		) || 
		is_writable($htaccess_file)
	) {
		if($mod_rewrite_enabled) {
			$h5bp_rules = extract_from_markers($htaccess_file, 'HTML5 Boilerplate');
			
			// If there are no Boilerplate Rules and the user wants them, add them.
			if ($h5bp_rules === array() && $site_options['html5_bp'] === true) {
				$filename = dirname(__FILE__) . '/../support/H5BPv4.3_htaccess';
				$boilerplate_rules = extract_from_markers($filename, 'HTML5 Boilerplate');
			
			// If there are Boilerplate rules and the user doesn't want them, remove them.
			} else if($h5bp_rules !== array() && $site_options['html5_bp'] !== true) {
				$boilerplate_rules = '';
			}
			
			// Update the HTACCESS file.
			insert_with_markers($htaccess_file, 'HTML5 Boilerplate', $boilerplate_rules);
		}
	}
	
	return $content;
}
add_action('generate_rewrite_rules', 'launchpad_add_h5bp_htaccess');


/**
 * The Preg Callback for Root Relative URLS
 *
 * @param		array $matches The preg matches.
 * @see			launchpad_root_relative_url()
 * @since   	Version 1.0
 */
function launchpad_root_relative_url_preg_callback($matches) {
	if (
		isset($matches[0]) && 
		$matches[0] === home_url("/") && 
		str_replace("http://", "", home_url("/", "http")) == $_SERVER["HTTP_HOST"]
	) { 
		return "/";
	} else if (isset($matches[0]) && strpos($matches[0], home_url("/")) !== false) { 
		return $matches[2];
	} else { 
		return $matches[0]; 
	}
}

/**
 * Make links root-relative
 *
 * Strips out the base URL so that paths are root-relative instead of including the entire domain name.
 * Less verbosity is good, but it also makes databases more portable.
 *
 * $launchpad_rel_filters below contains the list of filters that get the substitution.
 *
 * This is modified from the Roots theme.
 *
 * @param		text $input The string to make root-relative
 * @since   	Version 1.0
 */
function launchpad_root_relative_url($input) {
	$output = preg_replace_callback(
		'!(https?://[^/|"]+)([^"]+)?!',
		'launchpad_root_relative_url_preg_callback',
		$input
	);

	return $output;
}

// The filters to have relative paths fixed on.
$launchpad_rel_filters = array(
		'bloginfo_url',
		'theme_root_uri',
		'stylesheet_directory_uri',
		'template_directory_uri',
		'plugins_url',
		'the_permalink',
		'wp_list_pages',
		'wp_list_categories',
		'wp_nav_menu',
		'the_content_more_link',
		'the_tags',
		'get_pagenum_link',
		'get_comment_link',
		'month_link',
		'day_link',
		'year_link',
		'tag_link',
		'the_author_posts_link',
		'wp_get_attachment_url',
		'attachment_link',
		
	);

// Apply each filter
foreach($launchpad_rel_filters as $launchpad_rel_filter) {
	add_filter($launchpad_rel_filter, 'launchpad_root_relative_url');
}


/**
 * Removes self-closing tags
 *
 * XHTML reqiuires self-closing tags to self-close like XML requires.  In HTML5, these are
 * supurfluous, so we can trim them off.
 *
 * This is modified from the Roots theme.
 *
 * @param		array $input The string to remove the self-closing part of the tag
 * @since   	Version 1.0
 */
function launchpad_remove_self_closing_tags($input) {
	return str_replace(' />', '>', $input);
}

add_filter('get_avatar', 'launchpad_remove_self_closing_tags');
add_filter('comment_id_fields', 'launchpad_remove_self_closing_tags');
add_filter('post_thumbnail_html', 'launchpad_remove_self_closing_tags');


/**
 * Modify image attributes
 *
 * Removes title attribute and makes alt attribute an empty string.  This removes the
 * burden from the user of having to come up with real names and makes images semantically
 * invisible to screen readers.
 *
 * @param		array $attr The image attributes to modify
 * @since   	Version 1.0
 */
function launchpad_wp_get_attachment_image_attributes( $attr ) {
	unset($attr['title']);
	$attr['alt'] = '';
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'launchpad_wp_get_attachment_image_attributes' );


/**
 * Save launchpad_meta fields
 *
 * @param		number $post_id The post ID that the meta applies to
 * @since   	Version 1.0
 */
function launchpad_save_post_data($post_id) {
	// Touch the API file to reset the appcache.
	// This helps avoid confusing issues with time zones.
	touch(str_replace('system.php', 'api.php', __FILE__), time(), time());
	
	// If there is no LaunchPad fields, don't affect anything.
	if(empty($_POST) || !isset($_POST['launchpad_meta'])) {
		return;
	}
	if($_POST['post_type'] === 'page') {
		if(!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if(!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	foreach($_POST['launchpad_meta'] as $meta_key => $meta_value) {
		update_post_meta($post_id, $meta_key, $meta_value);
	}
}
add_action('save_post', 'launchpad_save_post_data');
