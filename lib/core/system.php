<?php
/**
 * WordPress System Functions
 *
 * Functions that modify / augment the lower-level system (e.g. htaccess, image sizes, menus, and the like).
 *
 * @package 	Launchpad
 * @since		1.0
 */



/** Root-relative theme path. */
define('THEME_PATH', '/' . str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', get_template_directory()));
/** Root-relative child theme path. */
define('CHILD_THEME_PATH', '/' . str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', get_stylesheet_directory()));

if(!defined('LAUNCHPAD_VERSION')) {
	define('LAUNCHPAD_VERSION', '1.5');
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
	
	// Disable comments and such.
	update_option('default_pingback_flag', 0);
	update_option('default_ping_status', 0);
	update_option('default_comment_status', 0);
	
	if(get_option('blogdescription') == 'Just another WordPress site') {
		update_option('blogdescription', '');
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
if(is_admin()) {
	add_action('after_switch_theme', 'launchpad_theme_activation_action');
}


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
if(is_admin()) {
	add_action('after_switch_theme', 'launchpad_settings_redirect', 9999);
}


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
	return preg_replace('| />|', '>', $input);
}
add_filter('get_avatar', 'launchpad_remove_self_closing_tags');
add_filter('comment_id_fields', 'launchpad_remove_self_closing_tags');
add_filter('post_thumbnail_html', 'launchpad_remove_self_closing_tags');
if(is_admin()) {
	add_filter('image_send_to_editor', 'launchpad_remove_self_closing_tags');
}


/**
 * Modify image attributes
 *
 * Removes title attribute and makes alt attribute an empty string.  This removes the
 * burden from the user of having to come up with real names and makes images semantically
 * invisible to screen readers.
 *
 * @param		array $attr The image attributes to modify
 * @since		1.0
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


/**
 * Attempt to optimize an image with an available set of programs
 * 
 * @attr	string $file The path to the file to optimize
 * @since	1.5
 */
function launchpad_compress_image($file = false) {
	// Problem with file.
	if(!$file || !file_exists($file)) {// || !is_writable($path)) {
		return false;
	}
	
	// Problem with server.
	if(ini_get('safe_mode') && strpos(ini_get("disable_functions"), "exec") !== false) {
		return false;
	}
	
	// Check how we can compress.
	$pathinfo = pathinfo($file);
	switch(strtolower($pathinfo['extension'])) {
		case 'jpeg':
		case 'jpg':
			// JPEG options.
			$compressors = array(
				//'mozjpeg 2>&1' => "mozjpeg -copy none -outfile $file.optimized $file 2>&1",
				'jpegtran 2>&1' => "jpegtran -copy none -optimize -progressive -outfile $file.optimized $file 2>&1",
				'jpegoptim 2>&1' => "jpegoptim --strip-all --all-progressive --dest=$file.optimized $file 2>&1"
			);
		break;
		case 'png':
			// PNG options.
			$compressors = array(
				'pngout 2>&1' => "pngout -s0 -y -kt -ks -k0 $file && cp $file $file.optimized 2>&1",
				'optipng 2>&1' => "optipng -nc -nb -o7 -out $file.optimized $file 2>&1",
				'pngcrush 2>&1' => "pngcrush -reduce -brute $file $file.optimized 2>&1"
			);
		break;
		default:
			// Unsupported extension.
			return false;
		break;
	}
	
	// Loop the available compressors.
	foreach($compressors as $compressor => $comand) {
		// Exec to see if the compressor is installed.
		@exec($compressor, $output, $status);
		// A missing command should result in a 127 status.
		// If not, the program can be used.
		if($status !== 127) {
			// Exect the command.
			@exec($comand, $output, $status);
			// If the optimized file was created, we can move it over.
			if(file_exists("$file.optimized")) {
				// Try to copy.
				if(@copy("$file.optimized", $file)) {
					// Remove the optimized file.
					@unlink("$file.optimized");
					// Return true because it worked.
					return true;
				} else {
					// Removed the optimized file.
					@unlink("$file.optimized");
					// Return false because it didn't work.
					return false;
				}
			// The optimized file wasn't created.
			} else {
				return false;
			}
		}
	}
	// No compressor was found.
	return false;
}


/**
 * Filter to execute the optimization of each image.
 *
 * @param		array $meta The metadata for the attached file.
 * @since		1.5
 */
function launchpad_handle_uploaded_files($meta) {
	if(!$meta) {
		return;
	}
	
	$file = wp_upload_dir($meta['file']);
	$orig_file_name = pathinfo($meta['file'], PATHINFO_BASENAME);
	
	$upload_folder = $file['path'] . '/';
	@launchpad_compress_image($upload_folder . $orig_file_name);
	if(isset($meta['sizes'])) {
		foreach($meta['sizes'] as $size) {
			@launchpad_compress_image($upload_folder . $size['file']);
		}
	}
	
	return $meta;
}
if(is_admin()) {
	add_filter('wp_generate_attachment_metadata', 'launchpad_handle_uploaded_files', 900);
}


/**
 * Alert the admin if memory gets low.
 *
 * @since		1.0
 */
function launchpad_memory_warning() {
	// The total memory currently allowed for PHP.
	$memory = ini_get('memory_limit');
	
	// The current memory usage.
	$mem_used = memory_get_usage();
	
	// The peak memory usage.
	$mem_peak = memory_get_peak_usage();
	
	// Convert the memory limit to bytes.
	$mem_limit = trim($memory);
	$mem_suffix = strtolower($mem_limit[strlen($mem_limit)-1]);
	switch($mem_suffix) {
		case 'g':
			$mem_limit *= 1024;
		case 'm':
			$mem_limit *= 1024;
		case 'k':
			$mem_limit *= 1024;
	}
	
	// If the memory left is under 500KB, send an e-mail.
	if($mem_limit-$mem_peak < 512000) {
		
		// Build the message.
		$message = 'A page on your website is in danger of running out of memory or is running out of memory.  Please forward this e-mail to your web developer.  Diagnostic information is below.';
		
		$message .= "\n\n";
		
		$message .= 'URL: http' . ($_SERVER['SERVER_PORT'] != 80 ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
		$message .= 'PHP Memory Limit: ' . $mem_limit . " bytes\n";
		$message .= 'Memory Usage: ' . $mem_used . " bytes\n";
		$message .= 'Memory Peak Usage: ' . $mem_peak . " bytes\n";
		$message .= 'Memory Left At Peak: ' . ($mem_limit-$mem_peak) . " bytes\n\n\n";

		$message .= '$_SERVER = ';			
		$message .= print_r($_SERVER, true) . "\n\n";
		
		// Send the mail.
		wp_mail(
			get_option('admin_email'), 
			'Warning: ' . get_bloginfo('name') . ' Low Memory',  
			 $message, 
			'From: ' . get_bloginfo('name') . ' <no-reply@' . $_SERVER['HTTP_HOST'] . '>'
		);
	}
}
register_shutdown_function('launchpad_memory_warning');
