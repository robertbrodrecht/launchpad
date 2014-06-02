<?php
/**
 * WordPress System Functions
 *
 * Functions that modify / augment the lower-level system (e.g. htaccess, image sizes, menus, and the like).
 *
 * @package 	Launchpad
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
 */
function launchpad_theme_activation_action() {
	// Default values.
	$home_page_name = 'Home';
	$articles_page_name = 'Articles';
	$articles_path = '/articles/%postname%/';
	$uploads_path = 'assets';
	
	// Apply filters so the developer can change the locations.
	$home_page_name = apply_filters('launchpad_activate_home_name', $home_page_name);
	$articles_page_name = apply_filters('launchpad_activate_articles_name', $articles_page_name);
	$articles_path = apply_filters('launchpad_activate_articles_path', $articles_path);
	$uploads_path = apply_filters('launchpad_activate_upload_path', $uploads_path);
	
	// If the developer didn't include the postname, add it for them.
	if(stristr($articles_path, '%postname%') === false) {
		$articles_path .= '/%postname%/';
	}
	
	// Fix some common issues.
	$uploads_path = preg_replace('|^/|', '', $uploads_path);
	$uploads_path = preg_replace('|/$|', '', $uploads_path);
	$uploads_path = preg_replace('|/+|', '/', $uploads_path);
	$articles_path = preg_replace('|/+|', '/', $articles_path);
	
	// Delete the default stuff.
	for($p = 1; $p < 3; $p++) {
		wp_delete_post($p, true);
	}
	
	// Create a home page.
	$page = new WP_Query('name=' . sanitize_title($home_page_name) . '&post_type=page');
	if($page->post_count > 0) {
		$home_page_id = $page->post->ID;
	} else {
		$home_page_id = wp_insert_post(
			array(
				'post_type' => 'page',
				'post_author' => 1,
				'post_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
				'post_name' => sanitize_title($home_page_name),
				'post_title' => $home_page_name,
				'post_status' => 'publish'
			), false
		);
	}
	
	// Create an articles page.
	$page = new WP_Query('name=' . sanitize_title($articles_page_name) . '&post_type=page');
	if($page->post_count > 0) {
		$articles_page_id = $page->post->ID;
	} else {
		$articles_page_id = wp_insert_post(
			array(
				'post_type' => 'page',
				'post_author' => 1,
				'post_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
				'post_name' => sanitize_title($articles_page_name),
				'post_title' => $articles_page_name,
				'post_status' => 'publish'
			), false
		);
	}
	
	// Update options.
	if($home_page_id > 0 && $articles_page_id > 0) {
		update_option('show_on_front', 'page');
		update_option('page_for_posts', $articles_page_id);
		update_option('page_on_front', $home_page_id);
	}
	
	update_option('permalink_structure', $articles_path);
	update_option('upload_path', $uploads_path);
	
	// Flush rewrite rules when settings are saved.
	flush_rewrite_rules(true);
}
add_action('after_switch_theme', 'launchpad_theme_activation_action');


/**
 * Handle Settings Redirect After Theme Switch
 *
 * @since		1.0
 */
function launchpad_settings_redirect() {
	global $pagenow;
	if (is_admin() && isset($_GET['activated']) && $pagenow == 'themes.php') {
		header('Location: options-general.php?page=launchpad_settings');
		exit;
	}
}
add_action('after_switch_theme', 'launchpad_settings_redirect', 9999);


/**
 * Add Specific Headers
 *
 * @since		1.0
 */
function launchpad_http_headers() {
	header('X-UA-Compatible: IE=edge,chrome=1');
	if(isset($_GET['launchpad_ajax'])) {
		header('HTTP/1.0 200 OK', true, 200);
	}
}
add_action('send_headers', 'launchpad_http_headers');


/**
 * The Preg Callback for Root Relative URLS
 *
 * @param		array $matches The preg matches.
 * @see			launchpad_root_relative_url()
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
 */
function launchpad_remove_self_closing_tags($input) {
	return str_replace(' ?/>', '>', $input);
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
 * @since		1.0
 * @todo		Why is this not doing what I think it should?
 */
function launchpad_wp_get_attachment_image_attributes($attr) {
	unset($attr['title']);
	$attr['alt'] = '';
	return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'launchpad_wp_get_attachment_image_attributes');



/**
 * Allow SVG Uploads
 *
 * @param		array $mimes Existing mime-types.
 * @since		1.0
 */
function launchpad_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'launchpad_mime_types');
