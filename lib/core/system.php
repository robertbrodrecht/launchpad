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
	$file = wp_upload_dir($meta['file']);
	$orig_file_name = pathinfo($meta['file'], PATHINFO_BASENAME);
	
	$upload_folder = $file['path'] . '/';
	@launchpad_compress_image($upload_folder . $orig_file_name);
	if($meta['sizes']) {
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


















/**
 * Verify Communication Key
 *
 * @since		1.5
 */
function launchpad_validate_communication_key() {
	$communication_key = get_transient('launchpad_migration_communication_key');
	if($communication_key) {
		header('Access-Control-Allow-Origin: *');
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	echo @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) ? 1 : 0;
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_validate_communication_key', 'launchpad_validate_communication_key');
	add_action('wp_ajax_nopriv_launchpad_validate_communication_key', 'launchpad_validate_communication_key');
}


/**
 * Delete Communication Key
 *
 * @since		1.5
 */
function launchpad_migration_clear_key() {
	delete_transient('launchpad_migration_communication_key');
	echo 1;
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migration_clear_key', 'launchpad_migration_clear_key');
	add_action('wp_ajax_nopriv_launchpad_migration_clear_key', 'launchpad_migration_clear_key');
}


/**
 * Truncate a Table
 *
 * @since		1.5
 */
function launchpad_migrate_truncate_table() {
	global $wpdb;
	
	$communication_key = get_transient('launchpad_migration_communication_key');
	if(@openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) == false) {
		echo '0';
		return;
	}
	if($communication_key) {
		header('Access-Control-Allow-Origin: *');
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	$table = $_GET['table'];
	$table = @openssl_decrypt($table, 'aes128', $communication_key);
	if(!$table) {
		echo '0';
		return;
	}
	echo $wpdb->query('TRUNCATE TABLE ' . $table);	
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_truncate_table', 'launchpad_migrate_truncate_table');
	add_action('wp_ajax_nopriv_launchpad_migrate_truncate_table', 'launchpad_migrate_truncate_table');
}


/**
 * Migrate a Table Row
 *
 * @since		1.5
 */
function launchpad_migrate_table() {
	global $wpdb;
	
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	if(@openssl_decrypt($_POST['communication_test'], 'aes128', $communication_key) == false) {
		echo '0';
		exit;
	}
	if($communication_key) {
		header('Access-Control-Allow-Origin: *');
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	
	$_POST['row'] = @openssl_decrypt($_POST['row'], 'aes128', $communication_key);
	if(!$_POST['row']) {
		echo '0';
		exit;
	}
	$row = csv_to_array(base64_decode($_POST['row']));
	$table = @openssl_decrypt($_POST['table'], 'aes128', $communication_key);
	if(!$table) {
		echo '0';
		exit;
	}
	
	$table = $wpdb->prefix . $table;
	
	$columns = $wpdb->get_results('SHOW columns FROM ' . $table);
	
	$q = '';
	
	foreach($columns as $column) {
		if($q) {
			$q .= ', ';
		}
		$q .= '`' . $column->Field . '` = %s';
	}
	
	$query = false;
	if($q) {
		$query = 'REPLACE INTO `' . $table . '` SET ' . $q;
	}
	
	if($query) {
		$results = $wpdb->query(
			$wpdb->prepare(
				$query,
				$row
			)
		);
	}
	
	if($_POST['file_path'] != '0') {
		
		$file_path = get_attached_file($row[0]);
		
		$f = fopen($file_path, 'w');
		fwrite($f, base64_decode($_POST['file']));
		fclose($f);
		
		launchpad_do_regenerate_image($row[0]);
	}
	
	echo $results;
	
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_table', 'launchpad_migrate_table');
	add_action('wp_ajax_nopriv_launchpad_migrate_table', 'launchpad_migrate_table');
}


/**
 * Generate A Table List
 *
 * @since		1.5
 */
function launchpad_migrate_get_tables() {
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	if(@openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) == false) {
		echo '0';
		exit;
	}
	if($communication_key) {
		header('Access-Control-Allow-Origin: *');
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	
	$export_files = launchpad_generate_database_csv();
	echo @openssl_encrypt(json_encode($export_files), 'aes128', $communication_key);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_get_tables', 'launchpad_migrate_get_tables');
	add_action('wp_ajax_nopriv_launchpad_migrate_get_tables', 'launchpad_migrate_get_tables');
}


/**
 * Generate A Table Row
 *
 * @since		1.5
 */
function launchpad_migrate_get_table_row() {
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	if(@openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) == false) {
		echo '0';
		exit;
	}
	if($communication_key) {
		header('Access-Control-Allow-Origin: *');
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	
	$table_file = @openssl_decrypt($_GET['table'], 'aes128', $communication_key);
	
	if(isset($_GET['unlink'])) {
			echo '';
			unlink($table_file);
			exit;
	}
	
	if(file_exists($table_file)) {
		if(!filesize($table_file)) {
			echo '';
			unlink($table_file);
			exit;
		}
		$fin = fopen($table_file, 'r');
		$fout = fopen($table_file . '.tmp', 'w');
		$first_line = false;
		while(!feof($fin)) {
			$cur_line = fgetcsv($fin);
			if($cur_line) {
				if($first_line) {
					fputcsv($fout, $cur_line);
				} else {
					$first_line = $cur_line;
				}
			}
		}
		fclose($fin);
		fclose($fout);
		rename($table_file . '.tmp', $table_file);
		if(!filesize($table_file)) {
			echo '';
			unlink($table_file);
			exit;
		}
		echo @openssl_encrypt(json_encode($first_line), 'aes128', $communication_key);
	} else {
		echo '';
	}
	
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_get_table_row', 'launchpad_migrate_get_table_row');
	add_action('wp_ajax_nopriv_launchpad_migrate_get_table_row', 'launchpad_migrate_get_table_row');
}


/**
 * Generate CSV Data
 *
 * @since		1.5
 */
function launchpad_generate_database_csv() {
	global $wpdb;

	$res = array();
	
	$tables = $wpdb->get_results('show tables', ARRAY_A);
	foreach($tables as $table) {
		$table = array_pop($table);
		if(preg_match('/^' . $wpdb->prefix . '/', $table)) {
			$cache_file = launchpad_get_cache_file('migration-' . date('Y-m-d-H:i:s') . '-' . $table);
			
			$res[preg_replace('/^' . $wpdb->prefix . '/', '', $table)] = $cache_file;
			
			$cache_file = fopen($cache_file, 'w');
			
			$records = $wpdb->get_results('SELECT count(*) as cnt FROM `' . $table . '`', ARRAY_A);
			$records = array_pop($records);
			$records = array_pop($records);
			
			$rows_per_page = 100;
			$max_pages = ceil($records/$rows_per_page);
			
			for($current_offet = 0; $current_offet/$rows_per_page < $max_pages; $current_offet = $current_offet + $rows_per_page) {
				$results = $wpdb->get_results('SELECT * FROM `' . $table . '` LIMIT ' . $current_offet . ', ' . $rows_per_page, ARRAY_A);
				foreach($results as $result) {
					fputcsv($cache_file, $result);
				}
			}
			fclose($cache_file);
		}
	}
	
	return $res;
}


/**
 * Add an admin page for migration.
 *
 * @since		1.5
 */
function launchpad_add_migration_page() {
	add_submenu_page('tools.php', 'Migrate', 'Migrate', 'update_core', 'launchpad/migrate/', 'launchpad_render_migrate_admin_page', 99);
}
if(is_admin() && ($_SERVER['HTTP_HOST'] === 'launchpad.git' || $_SERVER['HTTP_HOST'] === 'launchpad2.git')) {
	add_action('admin_menu', 'launchpad_add_migration_page');
}


/**
 * Recursively Replace Hosts
 * 
 * @since		1.5
 */
function launchpad_migrate_domain_replace($input = '', $local, $remote) {
	if(is_array($input) || is_object($input)) {
		foreach($input as &$child) {
			$child = launchpad_migrate_domain_replace($child, $local, $remote);
		}
	} else {
		$input = str_replace($local, parse_url($remote, PHP_URL_HOST), $input);
	}
	return $input;
}


/**
 * Display the Admin Migration Page
 * 
 * Don't try to use this yet.  I'm still researching security implications.
 * 
 * @since		1.5
 * @todo		Improve errors.
 */
function launchpad_render_migrate_admin_page() {
	global $wpdb;
	
	$form = 'start';
	
	$communication_key = get_transient('launchpad_migration_communication_key');
	if(!$communication_key) {
		set_transient('launchpad_migration_communication_key', md5(serialize(wp_get_current_user()) . time()), 60 * 10);
		$communication_key = get_transient('launchpad_migration_communication_key');
	}
	if($communication_key) {
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	
	$errors = array();
	
	if(!empty($_POST) && isset($_POST['migrate_action'])) {
		switch($_POST['migrate_action']) {
			case 'verify':
				$local_version = file_get_contents(site_url() . '/api/?action=launchpad_version');
				if(!isset($_POST['migrate_url']) || empty($_POST['migrate_url'])) {
					$errors[] = 'You must specify the remote URL.';
				} else {
					$remote_version = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_version');
					if($local_version != $remote_version) {
						$errors[] = 'The remote site is on Launchpad ' . $remote_version . ' while this site is on ' . $local_version . '. You must upgrade Launchpad on both sites to the same version.';
					} else {
						$remote_key_valid = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_validate_communication_key&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])));
						
						if($remote_key_valid == '0') {
							$errors[] = 'Please verify that your communication key is correct.';
						}
					}
				}
				if(!$errors) {
					$form = 'options';
				}
			break;
			case 'migrate':
				$remote_key_valid = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_validate_communication_key&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])));
				if($remote_key_valid) {
					if($_POST['migrate_direction'] === 'push') {
						foreach($_POST['migrate_database'] as $table => $file) {
							set_time_limit(60*5);
							if(file_exists($file)) {
								if($table != 'options') {
									$truncate_success = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_truncate_table&table=' . @openssl_encrypt($table, 'aes128', $_POST['communication_key']) . '&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])));
								}
								
								$data = fopen($file, 'r');
								while(!feof($data)) {
									$row = fgetcsv($data);
									if($row) {
										if($table == 'options') {
											if(
												$row[1] == '_transient_launchpad_migration_communication_key' ||
												$row[1] == '_transient_timeout_launchpad_migration_communication_key'
											) {
												break;
											}
										}
										if($table == 'usermeta') {
											if(
												$row[2] == 'session_tokens'
											) {
												$row[3] = '';
											}
										}
										
										$att_file = false;
										if(isset($_POST['migrate_attached_files'])) {
											if($table == 'posts' && $row[20] === 'attachment') {
												$att_file = wp_get_attachment_url($row[0]);
												$att_file =  $att_file;
											}
										}
										
										foreach($row as &$col) {
											if(is_serialized($col)) {
												$tmp_col = unserialize($col);
												$tmp_col = launchpad_migrate_domain_replace($tmp_col, $_SERVER['HTTP_HOST'], $_POST['migrate_url']);
												$col = serialize($tmp_col);
											} else {
												$col = launchpad_migrate_domain_replace($col, $_SERVER['HTTP_HOST'], $_POST['migrate_url']);
											}
										}
										
										$row = array_to_csv($row);
										$row = base64_encode($row);
										
										$postdata = http_build_query(
										    array(
										        'row' => @openssl_encrypt($row, 'aes128', $_POST['communication_key']),
										        'table' => @openssl_encrypt($table, 'aes128', $_POST['communication_key']),
										        'communication_test' => @openssl_encrypt('communication', 'aes128', $_POST['communication_key']),
										        'action' => 'launchpad_migrate_table',
										        'file' => $att_file ? base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $att_file)) : '0',
										        'file_path' => $att_file ? $att_file : '0'
										    )
										);
										
										$opts = array('http' =>
										    array(
										        'method'  => 'POST',
										        'header'  => 'Content-type: application/x-www-form-urlencoded',
										        'content' => $postdata
										    )
										);
										
										$context = stream_context_create($opts);
										$result = file_get_contents($_POST['migrate_url'] . '/api/', false, $context);
									}
								}
								fclose($data);
								unlink($file);
							}
						}
						$remote_version = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migration_clear_key');
						$form = 'database_complete';
					} else if($_POST['migrate_direction'] === 'pull') {
						foreach($_POST['migrate_database'] as $table => $file) {
							set_time_limit(60*5);
							@unlink($file);
						}
						
						$table_list = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_tables&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])));
						
						if(!$table_list) {
							$errors[] = 'The remote key is no longer valid.';
							break;
						} else {
							$table_list = openssl_decrypt($table_list, 'aes128', $_POST['communication_key']);
							
							if(!$table_list) {
								$errors[] = 'Could not decrypt table list.';
								break;
							} else {
								$table_list = json_decode($table_list);
								foreach($table_list as $table => $file) {
									if(isset($_POST['migrate_database'][$table])) {
										do {
											set_time_limit(60*5);
											$rows = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_table_row&table=' . urlencode(@openssl_encrypt($file, 'aes128', $_POST['communication_key'])) . '&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])));
											
											if($rows) {
												$rows = openssl_decrypt($rows, 'aes128', $_POST['communication_key']);
												if($rows) {
													$rows = json_decode($rows);
													die(__FILE__);
												}
											}
										} while($rows);
									}
									file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_table_row&table=' . urlencode(@openssl_encrypt($file, 'aes128', $_POST['communication_key'])) . '&communication_test=' . urlencode(@openssl_encrypt('communication', 'aes128', $_POST['communication_key'])) . '&unlink=true');
								}
								exit;
							}
						}
					}
				} else {
					$errors[] = 'The remote key is no longer valid.';
				}
			break;
		}
	}
	
	if(isset($_POST['migrate_url'])) {
		set_transient('launchpad_migration_remote_url', $_POST['migrate_url']);
	} else {
		$_POST['migrate_url'] = get_transient('launchpad_migration_remote_url');
	}
	
	if(isset($_POST['communication_key'])) {
		set_transient('launchpad_migration_remote_communication_key', $_POST['communication_key'], 60 * 60);
	} else {
		$_POST['communication_key'] = get_transient('launchpad_migration_remote_communication_key');
	}
	
	?>
	<div class="wrap">
		<h2>Database Migration</h2>
		<div class="error"><p><strong>SUPER BETA!</strong>  You probably shouldn't use this in production.  It's not well tested and almost certainly will break plugins that use serialized data with URLs.</p></div>
		<?php

			if($errors) {
				echo '<h3>Errors were encountered!</h3>';
				foreach($errors as $error) {
					echo "<li>$error</li>";
				}
				echo '</ul>';
			} else {

		?>
		<p>If this site is the remote site, the communication key is: <?= $communication_key ?></p>
		<p>The will be invalid after 10 minutes of inactivity.</p>
		<?php } ?>
		<form method="post" id="poststuff">
			<?php
			
			switch($form) {
				default:
					?>
					<div class="postbox">
						<h3 class="hndle"><span>Setup</span></h3>
						<div class="inside">
							<div class="launchpad-metabox-field">
								<label>
									Full URL to Remote Site
									<input type="text" name="migrate_url" value="<?= $_POST['migrate_url'] ?>">
								</label>
							</div>
							<div class="launchpad-metabox-field">
								<label>
									Remote Site's Communication Key
									<input type="text" name="communication_key" value="<?= $_POST['communication_key'] ?>">
								</label>
							</div>
						</div>
					</div>
					<div>
						<input type="hidden" name="migrate_action" value="verify">
						<input type="submit" class="button button-primary button-large" value="Verify Settings">
					</div>
					<?php
				
				break;
				case 'options':
					
					?>
					<div class="postbox">
						<h3 class="hndle"><span>Actions</span></h3>
						<div class="inside">
							<fieldset class="launchpad-metabox-fieldset">
								<legend>Action to Perform</legend>
								<div class="launchpad-metabox-field">
									<label>
										<input type="radio" name="migrate_direction" value="push"<?= !isset($_POST['migrate_direction']) || $_POST['migrate_direction'] == 'push' ? ' checked="checked"' : '' ?>>
										Push This Site to Remote
									</label>
								</div>
								<div class="launchpad-metabox-field">
									<label>
										<input type="radio" name="migrate_direction" value="pull"<?= isset($_POST['migrate_direction']) && $_POST['migrate_direction'] == 'pull' ? ' checked="checked"' : '' ?>>
										Pull Remote to This Site
									</label>
								</div>
							</fieldset>
							<fieldset class="launchpad-metabox-fieldset">
								<legend>Options</legend>
								<div class="launchpad-metabox-field">
									<label>
										<input type="checkbox" name="migrate_attached_files" value="yes"<?= !isset($_POST['migrate_attached_files']) || $_POST['migrate_attached_files'] == 'yes' ? ' checked="checked"' : '' ?>>
										Update Attached Files (Slower)
									</label>
								</div>
							</fieldset>
							<fieldset class="launchpad-metabox-fieldset">
								<legend>Tables to Replace</legend>
								<?php
								
								$tables = launchpad_generate_database_csv();
								
								$opts_url = $tables['options'];
								unset($tables['options']);
								
								$tables['options'] = $opts_url;
								
								foreach($tables as $table => $file) {
									?>
									<div class="launchpad-metabox-field">
										<label>
											<input type="checkbox" name="migrate_database[<?= $table ?>]" value="<?= $file ?>"<?= !isset($_POST['migrate_database']) || $_POST['migrate_database'][$table] ? ' checked="checked"' : '' ?>>
											<?= $table ?>
										</label>
									</div>
									<?php
								}
								
								?>
							</fieldset>
						</div>
					</div>
					<div>
						<input type="hidden" name="migrate_action" value="migrate">
						<input type="hidden" name="migrate_url" value="<?= $_POST['migrate_url'] ?>">
						<input type="hidden" name="communication_key" value="<?= $_POST['communication_key'] ?>">
						<input type="submit" class="button button-primary button-large" value="Start Migration">
					</div>
					<?php
						
				break;
				case 'database_complete':
					
					?>
					<p><strong>The database import is complete.</strong></p>
					<?php
						
				break;
			}
			?>
		</form>
	</div>
	<?php
}