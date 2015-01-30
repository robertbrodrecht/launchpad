<?php
/**
 * WordPress Tools
 *
 * Special tools that make development easier.
 *
 * @package 	Launchpad
 * @since		1.3
 */
 
 
/**
 * Add a field to media that will allow for media replacement.
 * 
 * @since		1.3
 */
function launchpad_add_media_replacement_fields($post_types) {
	$custom_post_types = array(
		'attachment' => array(
			'metaboxes' => array(
				'launchpad_replace' => array(
					'name' => 'Media Replace',
					'help' => '<p>Upload a new file to replace this one.  It will use the same file name.</p>',
					'location' => 'side',
					'position' => 'default',
					'fields' => array(
						'replacement' => array(
							'name' => 'New File',
							'help' => '<p>Upload a new file that will replace this file.  This file will be renamed to the name of the existing file, so it would be wise of you to replace the file with the same media (e.g. a JPEG with a JPEG).  This is done to preserve direct links to the file.</p>',
							'args' => array(
								'type' => 'file'
							)
						)
					)
				)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
add_filter('launchpad_custom_post_types', 'launchpad_add_media_replacement_fields', 1);


/**
 * Save launchpad_meta fields
 *
 * @param		number $post_id The post ID that the meta applies to
 * @since		1.0
 */
function launchpad_handle_media_replace($post) {
	if(!$replace_id = $post['launchpad_meta']['replacement']) {
		return $post;
	}
	
	$post_id = $post['ID'];
	$replace_id = $post['launchpad_meta']['replacement'];
	
	$original_source = get_attached_file($post_id);
	$replace_source = get_attached_file($replace_id);
	
	if($original_source && $replace_source) {
		$imginfo = pathinfo($original_source);
		$all_files = scandir($imginfo['dirname']);
		foreach($all_files as $all_file) {
			if(preg_match('/' . $imginfo['filename'] . '-\d+x\d+\.(jpeg|jpg|png|gif)$/i', $all_file)) {
				unlink($imginfo['dirname'] . DIRECTORY_SEPARATOR . $all_file);
			}
		}
		
		unlink($original_source);
		copy($replace_source, $original_source);
		
		update_attached_file($post_id, $original_source);
		$metadata = wp_generate_attachment_metadata($post_id, $original_source);
		wp_update_attachment_metadata($post_id, $metadata);
	}
	
	return $post;
}
if(is_admin()) {
	add_action('attachment_fields_to_save', 'launchpad_handle_media_replace', 1);
}


/**
 * Add entry in Tools for regen thumbnails
 * 
 * @since		1.3
 */
function launchpad_register_tools() {
	add_management_page(
		'Regenerate Thumbnails', 
		'Regen Thumbnails', 
		'edit_files', 
		'launchpad-regenerate-thumbnails',
		'launchpad_display_regenerate_thumbnails_page'
	);	
}

if(is_admin()) {
	add_action('admin_menu', 'launchpad_register_tools');
}

function launchpad_display_regenerate_thumbnails_page() {
	?>
	<div class="wrap">
		<h2>Regenerate Thumbnails</h2>
		<p>
			Using this will delete all of your thumbnails and create new ones.  <strong><em>Leave now if you don't know what you're doing.</em></strong> This can be a long, server-taxing process, and if you bail half way through, you may lose data.  You may also spontaneously combust.  Stranger things have happened.
		</p>
	</div>
	<p>
		<input type="button" class="button button-primary button-large" value="Start Regenerating Thumbnails" id="start-regen">
	</p>
	<h2 id="launchpad-processing-percent"></h2>
	<div id="launchpad-regen-thumbnail-status"></div>
	<?php
}


/**
 * Get a list of attached images.
 * 
 * @since		1.3
 */
function launchpad_get_attachment_list() {
	global $wpdb;
	
	check_ajax_referer('launchpad-admin-ajax-request', 'nonce');
	
	$image_records = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC");
	
	$images = array();
	foreach($image_records as $image_record) {
		$images[] = (int) $image_record->ID;
	}
	
	header('Content-type: application/json');
	echo json_encode($images);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_get_attachment_list', 'launchpad_get_attachment_list');
}


/**
 * Regenerate thumbnails for a specified ID.
 * 
 * @since		1.3
 */
function launchpad_do_regenerate_image($att_id = false) {
	$internal = false;
	
	if(!$att_id) {
		header('Content-type: application/json');
		check_ajax_referer('launchpad-admin-ajax-request', 'nonce');
		$att_id = $_GET['attachment_id'];
	} else {
		$internal = true;
	}
	
	$assets = wp_upload_dir();
	
	$original_source = get_attached_file($att_id);
	
	if($original_source && file_exists($original_source)) {
		$imginfo = pathinfo($original_source);
		$all_files = scandir($imginfo['dirname']);
		foreach($all_files as $all_file) {
			if(preg_match('/' . $imginfo['filename'] . '-\d+x\d+\.(jpeg|jpg|png|gif)$/i', $all_file)) {
				unlink($imginfo['dirname'] . DIRECTORY_SEPARATOR . $all_file);
			}
		}
		$metadata = wp_generate_attachment_metadata($att_id, $original_source);
		wp_update_attachment_metadata($att_id, $metadata);
		
	} else {
		if($internal) {
			return array(
					'attachment_id' => $att_id, 
					'status' => 0, 
					'message' => 'Could not find original source file.'
				);
		}
		echo json_encode(
			array(
				'attachment_id' => $att_id, 
				'status' => 0, 
				'message' => 'Could not find original source file.'
			)
		);
		exit;
	}
	if($internal) {
		return array(
			'attachment_id' => $att_id, 
			'status' => 1, 
			'fullsrc' => $original_source
		);
	}
	
	echo json_encode(
		array(
			'attachment_id' => $att_id, 
			'status' => 1, 
			'fullsrc' => $original_source
		)
	);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_do_regenerate_image', 'launchpad_do_regenerate_image');
}


/**
 * Verify Communication Key
 *
 * @since		1.5
 */
function launchpad_validate_communication_key() {
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// If it exists, we can try to decrypt the key.
	if($communication_key) {
		$decrypt_status = @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key);
		
		// If the decrypt is successful, allow cross-origin and
		// set pushe the key expiratin out 10 minutes.
		if($decrypt_status === $nonce) {
			header('Access-Control-Allow-Origin: *');
			set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
			echo json_encode(
				array(
					'status' => true,
					'message' => 'Communication key and nonce are valid.'
				)
			);
		} else {
			echo json_encode(
				array(
					'status' => false,
					'message' => 'Communication key or nonce are invalid.  Please go to the remote site and verify the communication key.'
				)
			);
		}
		exit;
	} else {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Communication key has not been generated.'
			)
		);
		exit;
	}
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
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) !== $nonce) {
		echo '0';
		return;
	}
	
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
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) !== $nonce) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Communication key or nonce are invalid.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	// Allow CORs and extend the key by 10 minutes.
	header('Access-Control-Allow-Origin: *');
	set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	
	// Get the table and decrypt it.
	$table = $_GET['table'];
	$table_tmp = $table;
	$table = @openssl_decrypt($table, 'aes128', $communication_key);
	
	// If it fails to decrypt, quit.
	if($table === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Failed to decrypt table.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	if(!$table) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Missing table name. No operation executed.'
			)
		);
		exit;
	}
	
	// Truncate the table.
	$truncate = $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . $table);
	if($truncate) {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'Table truncated successfully.'
			)
		);
	} else {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Table failed to truncate. This error is not detrimental, but could lead to unexpected results.'
			)
		);
	}
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
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_test'], 'aes128', $communication_key) !== $nonce) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Communication key or nonce are invalid.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	// Allow CORs and extend the key by 10 minutes.
	header('Access-Control-Allow-Origin: *');
	set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	
	// Try to decrypt the row.
	$_POST['row'] = @openssl_decrypt($_POST['row'], 'aes128', $communication_key);
	
	// If it fails, quit here.
	if(!$_POST['row']) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt row data.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	// Try to convert the row data to an array.
	$row = csv_to_array(base64_decode($_POST['row']));
	
	// Decrypt the table.
	$table = @openssl_decrypt($_POST['table'], 'aes128', $communication_key);
	
	// If no table or row, quit.
	if(!$table || !$row) {
		echo '0';
		exit;
	}
	
	// Prefix the table so it is easier to work with.
	$table = $wpdb->prefix . $table;
	
	// Get the table's columns to build the query.
	$columns = $wpdb->get_results('SHOW columns FROM ' . $table);
	
	// Empty query.
	$query_fields = '';
	
	// Loop the columns to create a field list.
	foreach($columns as $column) {
		if($query_fields) {
			$query_fields .= ', ';
		}
		$query_fields .= '`' . $column->Field . '` = %s';
	}
	
	// If there are query fields, build the REPLACE query.
	$query = false;
	if($query_fields) {
		$query = 'REPLACE INTO `' . $table . '` SET ' . $query_fields;
	}
	
	// If there is a query, execute the query.
	if($query) {
		$results = $wpdb->query(
			$wpdb->prepare(
				$query,
				$row
			)
		);
		
		if(!$results) {
			echo json_encode(
				array(
					'status' => false,
					'message' => 'Could not add record #' . $row[0] . ' to ' . $table . '.' . ($_POST['file_path'] != '0' ? ' Additionally, the attached file was not uploaded.' : '')
				)
			);
			exit;
		}
	}
	
	// If a file path was sent, we need to pull the file.
	if($_POST['file_path'] != '0') {
		
		// Get the full path to the attached file.
		$file_path = get_attached_file($row[0]);
		
		// Open the file on this machine for writing..
		$f = fopen($file_path, 'w');
		if($f) {
			// Input the file data.
			fwrite($f, base64_decode($_POST['file']));
			// Close the file.
			fclose($f);
		
			// Re-generate the images for the file.
			launchpad_do_regenerate_image($row[0]);
		} else {
			echo json_encode(
				array(
					'status' => false,
					'message' => 'Could not write file attachment for record #' . $row[0] . ' on ' . $table . '.' . (file_exists($file_path) ? ' The old file still exists unmodified.' : ' Either this is a new file or the old file has been removed.  A link to this file will produce a 404.')
				)
			);
			exit;
		}
	}
	
	// Show the results.
	echo json_encode(
		array(
			'status' => true,
			'message' => 'The record and any attachments were successfully imported.'
		)
	);
	
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
 * @uses		launchpad_generate_database_csv
 */
function launchpad_migrate_get_tables() {
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) !== $nonce) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt row data.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	// Allow CORs and extend the key by 10 minutes.
	header('Access-Control-Allow-Origin: *');
	set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	
	// Generate all the database files.
	$export_files = launchpad_generate_database_csv();
	
	// Encrypt and output the results.
	echo json_encode(
		array(
			'status' => true,
			'message' => 'Tables exported successfully.',
			'data' => @openssl_encrypt(json_encode($export_files), 'aes128', $communication_key)
		)
	);
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
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_GET['communication_test'], 'aes128', $communication_key) !== $nonce) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt row data.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	// Allow CORs and extend the key by 10 minutes.
	header('Access-Control-Allow-Origin: *');
	set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	
	// Try to decrypt the table file path.
	$table_file = @openssl_decrypt($_GET['table'], 'aes128', $communication_key);
	if(!$table_file) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt table data file name.  Please go to the remote site and verify the communication key.'
			)
		);
		exit;
	}
	
	// If unlink is set and there is a table file and the table file is an HTML file
	if(isset($_GET['unlink']) && $table_file && pathinfo($table_file, PATHINFO_EXTENSION) === 'html') {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'Table export file deleted.',
				'data' => ''
			)
		);
		@unlink($table_file);
		exit;
	// Otherwise, report an error if this is an unlink request.
	} else if(isset($_GET['unlink'])) {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'Table export file already deleted.',
				'data' => ''
			)
		);
		exit;
	}
	
	// If the table field exists and it is an HTML file.
	if(file_exists($table_file) && pathinfo($table_file, PATHINFO_EXTENSION) === 'html') {
		
		// If the file is empty, delete it.
		if(!filesize($table_file)) {
			echo json_encode(
				array(
					'status' => true,
					'message' => 'Table export file deleted.',
					'data' => ''
				)
			);
			@unlink($table_file);
			exit;
		}
		
		// Open the table file for reading.
		$fin = fopen($table_file, 'r');
		
		// Open a temp file for writing.
		$fout = fopen($table_file . '.tmp', 'w');
		
		// This will check to see if we have gotten the first line to return.
		$first_line = false;
		
		// Loop the table file.
		while(!feof($fin)) {
			
			// Get the current CSV line.
			$cur_line = fgetcsv($fin);
			
			// If the line isn't empty, we can act on it.
			if($cur_line) {
				// If the first line has been set, write the line to the temp file.
				if($first_line) {
					fputcsv($fout, $cur_line);
				
				// Otherwise, set the first line as the first line.
				} else {
					$first_line = $cur_line;
				}
			}
		}
		
		// Close the input and output and rename the temp.
		fclose($fin);
		fclose($fout);
		rename($table_file . '.tmp', $table_file);
		
		// Encrypt the first line and send it out.
		echo json_encode(
			array(
				'status' => true,
				'message' => 'Row data found.',
				'data' => @openssl_encrypt(json_encode($first_line), 'aes128', $communication_key)
			)
		);
		exit;
	} else {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not find the table export file.  It may have been deleted.',
				'data' => ''
			)
		);
		exit;
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
	
	// Results containing table -> file associations.
	$res = array();
	
	// Show all the database tables in the current database.
	$tables = $wpdb->get_results('show tables', ARRAY_A);
	
	// Loop the results to see whether we are acting on it.
	foreach($tables as $table) {
		// Pop off the first value since that will be the table name.
		$table = array_pop($table);
		
		// If the table name starts with the WP prefix, we can migrate it.
		// Non-prefixed tables are ignored.
		if(preg_match('/^' . $wpdb->prefix . '/', $table)) {
			// Get a cache file for the current table.
			$cache_file = launchpad_get_cache_file('migration-' . date('Y-m-d-H:i:s') . '-' . $table);
			
			// Add the table -> file association to the results.
			$res[preg_replace('/^' . $wpdb->prefix . '/', '', $table)] = $cache_file;
			
			// Open the cache file.
			$cache_file = fopen($cache_file, 'w');
			
			// Get the records.
			$records = $wpdb->get_results('SELECT count(*) as cnt FROM `' . $table . '`', ARRAY_A);
			// Pop off the row.
			$records = array_pop($records);
			// Pop off the value.
			$records = array_pop($records);
			
			// We'll do 100 rows at a time to avoid memory issues.
			$rows_per_page = 100;
			// Calculate the total pages we have to go through.
			$max_pages = ceil($records/$rows_per_page);
			
			// Loop for each page of results we will have.
			for($current_offet = 0; $current_offet/$rows_per_page < $max_pages; $current_offet = $current_offet + $rows_per_page) {
				// Get the records for the current page.
				$results = $wpdb->get_results('SELECT * FROM `' . $table . '` LIMIT ' . $current_offet . ', ' . $rows_per_page, ARRAY_A);
				// Loop the results and write them to the cache file.
				foreach($results as $result) {
					fputcsv($cache_file, $result);
				}
			}
			
			// Close the file.
			fclose($cache_file);
		}
	}
	
	// Output the results.
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
	// If the current item is an array or object, we need to recurse.
	if(is_array($input) || is_object($input)) {
		// Loop the items we need to recurse.
		foreach($input as &$child) {
			// Recurse.
			$child = launchpad_migrate_domain_replace($child, $local, $remote);
		}
	// Otherwise, we just replace the local host with the remote host.
	} else {
		$input = str_replace(parse_url($local, PHP_URL_HOST), parse_url($remote, PHP_URL_HOST), $input);
	}
	// Return the modified input.
	return $input;
}


/**
 * Display the Admin Migration Page
 * 
 * @since		1.5
 * @uses		launchpad_migrate_domain_replace
 */
function launchpad_render_migrate_admin_page() {
	global $wpdb;
	
	// By default, we start from the start.
	$form = 'start';
	
	// See if we have set a communication key to plug into the local site if this is a remote site.
	$communication_key = get_transient('launchpad_migration_communication_key');
	// If not, generate one for 10 minutes.
	if(!$communication_key) {
		set_transient('launchpad_migration_communication_key', md5(serialize(wp_get_current_user()) . time()), 60 * 10);
		$communication_key = get_transient('launchpad_migration_communication_key');
	}
	
	// If we have a communication key that we just set or that already existed,
	// extend the timeout by 10 minutes.
	if($communication_key) {
		set_transient('launchpad_migration_communication_key', $communication_key, 60 * 10);
	}
	
	// Empty place to put errors.
	$errors = array();
	
	// If we have POST and a migration action to perform, perform the action.
	if(!empty($_POST) && isset($_POST['migrate_action'])) {
		
		// Decide what action to perform.
		switch($_POST['migrate_action']) {
			
			// Verify the correctness of the remote details.
			case 'verify':
				// Get the current site's launchpad version.
				$local_version = json_decode(file_get_contents(site_url() . '/api/?action=launchpad_version'));
				$local_version = $local_version->version;
				
				// If there is no migrate url, we can't do anything.
				if(!isset($_POST['migrate_url']) || empty($_POST['migrate_url'])) {
					$errors[] = 'You must specify the remote URL.';
				
				// Otherwise, start verifying.
				} else {
					
					// Get the remote site's launchpad version.
					$remote_version = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_version&communication_test=' . urlencode(@openssl_encrypt('initialize', 'aes128', $_POST['communication_key']))));
					
					// To avoid any inconsistencies in migration, only allow migration between
					// the same Launchpad versions.
					if($local_version != $remote_version->version) {
						$errors[] = 'The remote site is on Launchpad ' . $remote_version . ' while this site is on ' . $local_version . '. You must upgrade Launchpad on both sites to the same version.';
					
					// Versions are the same.
					} else {
						// Send a test to see if the communication key is correct.
						$remote_key_valid = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_validate_communication_key&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key']))));
						
						// If not, we need an error.
						if(!$remote_key_valid || $remote_key_valid->status === false) {
							$errors[] = $remote_key_valid->message;
						}
					}
				}
				
				// If there are not errors, show the options form.
				if(!$errors) {
					$form = 'options';
				}
			break;
			
			// We need to migrate data from one site to the other.
			case 'migrate':
				
				// Get the remote site's launchpad version.
				$remote_version = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_version&communication_test=' . urlencode(@openssl_encrypt('initialize', 'aes128', $_POST['communication_key']))));
				
				
				if(isset($remote_version->nonce)) {
					// Do a quick ping to make sure the key has not expired.
					$remote_key_valid = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_validate_communication_key&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key']))));
				} else {
					$remote_key_valid = (object) array('status' => false, 'message' => 'Remote server did not reply with the correct information.  Please verify that the communication key is correct.');
				}
				
				// If not, we can start migrating.
				if($remote_key_valid->status === true) {
					echo '<div id="migrate-status" class="wrap">Starting migration.</div>';
					
					// If the user requested to push, we need to handle pushing the data.
					if($_POST['migrate_direction'] === 'push') {
						
						// Loop over the tables the user wanted to migrate.
						foreach($_POST['migrate_database'] as $table => $file) {
							
							// Increase the time limit to 5 minutes in case something gets slow.
							set_time_limit(60*5);
							
							// If the table cache file exists, we can start reading data.
							if(file_exists($file)) {
								// If the table is not the options table, send a request to truncate.
								// We don't truncate options because it might cause the site to 
								// freak out if someone hits a page during import.
								if($table != 'options') {
									$truncate_success = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_truncate_table&table=' . urlencode(@openssl_encrypt($table, 'aes128', $_POST['communication_key'])) . '&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key'])));
									
									$truncate_success = json_decode($truncate_success);
									
									if($truncate_success->status === false) {
										$errors[] = $truncate_success->message;
									}
								}
								
								// Open the data file.
								$data = fopen($file, 'r');
								
								$row_count = 0;
								
								// While there is data to be read, loop the file.
								while(!feof($data)) {
									// Get the current row of CSV.
									$row = fgetcsv($data);
									
									$row_count++;
									
									// If there is a row (e.g. it wasn't an empty line), act on it.
									if($row) {
										echo '<script>document.getElementById("migrate-status").innerHTML = "Exporting row ' . $row_count . ' of ' . $table . '."</script>';
										flush();
										
										// For options tables, we don't want to replace the migration keys
										// since that would break migration when the decrypt check runs.
										if($table == 'options') {
											if(
												preg_match('/^_transient_launchpad_/', $row[1])
											) {
												break;
											}
										}
										// If we're in the usermeta table, we want to zero-out the session tokens.
										// If you update the session tokens with the current site's tokens,
										// the user has to log in twice because the first attempt causes a 
										// missing expire date key in WP's session variables.  Instead,
										// zeroing-out the record just forces the user to log back in.
										if($table == 'usermeta') {
											if(
												$row[2] == 'session_tokens'
											) {
												$row[3] = '';
											}
										}
										
										// Placeholder to check if there is an attachment to import.
										$att_file = false;
										
										// If the user opted to import attachments.
										if(isset($_POST['migrate_attached_files'])) {
											// If we're on a posts table and the post type is an
											// attachment, we'll get the details about the file.
											if($table == 'posts' && $row[20] === 'attachment') {
												$att_file = wp_get_attachment_url($row[0]);
											}
										}
										
										// Loop the row columns and replace the local domain with the remote domain.
										foreach($row as &$col) {
											// If the column contains serialized content, unserialze first.
											if(is_serialized($col)) {
												$tmp_col = unserialize($col);
												$tmp_col = launchpad_migrate_domain_replace($tmp_col, 'http://' . $_SERVER['HTTP_HOST'], $_POST['migrate_url']);
												$col = serialize($tmp_col);
											
											// Otherwise, handle the replace.
											} else {
												$col = launchpad_migrate_domain_replace($col, 'http://' . $_SERVER['HTTP_HOST'], $_POST['migrate_url']);
											}
										}
										
										// Convert the row to CSV.
										$row = array_to_csv($row);
										// Base64 encode the file
										$row = base64_encode($row);
										
										// Build post data to send to the remote server.
										$postdata = http_build_query(
										    array(
											    // Encrypted row.
										        'row' => @openssl_encrypt($row, 'aes128', $_POST['communication_key']),
										        // Encrypted table name.
										        'table' => @openssl_encrypt($table, 'aes128', $_POST['communication_key']),
										        // Communication test.
										        'communication_test' => @openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key']),
										        // The action to perform.
										        'action' => 'launchpad_migrate_table',
										        // If there is a file, the base64 encoded content of the file.
										        'file' => $att_file ? base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $att_file)) : '0',
										        // If there is a file, the path to the file.
										        'file_path' => $att_file ? $att_file : '0',
										    )
										);
										
										// Set up the options for the context.
										$opts = array('http' =>
										    array(
										        'method'  => 'POST',
										        'header'  => 'Content-type: application/x-www-form-urlencoded',
										        'content' => $postdata
										    )
										);
										
										$context = stream_context_create($opts);
										
										// Post to the remote API.
										$result = json_decode(file_get_contents($_POST['migrate_url'] . '/api/', false, $context));
										if($result->status === false) {
											$errors[] = $result->message;
											if(count($errors) > 20) {
												$errors[] = 'Quit import due to excessive import errors.  This can indicate that the remote communication key has expired.';
												break 2;
											}
										}
									}
								}
								
								// Close and delete the data file.
								fclose($data);
								unlink($file);
							}
						}
						
						// Since we're done with the import, clear the migration key.
						// That avoids any chance of the key sticking around too long.
						$key_cleared = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migration_clear_key&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key'])));
						
						// Set the form to the database complete message.
						$form = 'database_complete';
						echo '<script>document.getElementById("migrate-status").innerHTML = ""; document.getElementById("migrate-status").style.display="none";</script>';
						flush();
					
					// The user specified to pull remote data.
					} else if($_POST['migrate_direction'] === 'pull') {
						// Our table files get generated automatically, so delete them.
						// We can't just loop what they checked because that would leave trash.
						// So, we're just going to hose the entire cache.
						// Since migration should be rare, this isn't too worrysome.
						launchpad_clear_all_cache();
						
						// Send a request to get a list of tables on the remote install.
						$table_list = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_tables&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key']))));
						
						// If we didn't get a table list, error out.
						if($table_list->status === false) {
							$errors[] = $table_list->message;
							break;
						
						// Otherwise, try to decrypt.
						} else {
							$table_list = openssl_decrypt($table_list->data, 'aes128', $_POST['communication_key']);
							
							// If the list doesn't decrypt, error out.
							if(!$table_list) {
								$errors[] = 'Could not decrypt table list.  Please verify the communication key.';
								break;
							// Otherwise, start importing.
							} else {
								
								// The list is JSON encoded, so decode it.
								$table_list = json_decode($table_list);
								
								// Loop each table.
								foreach($table_list as $table => $file) {
									$row_count = 0;
									
									// If the table was checked, we can import it.
									if(isset($_POST['migrate_database'][$table])) {
										
										// Truncate unless it is the options table.
										if($table !== 'options') {
											$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . $table);
										}
										
										// Start looping to import the file.
										do {
											
											// Re-up the time limit in case something gets slow.
											set_time_limit(60*5);
											$row_count++;
											
											// We're importing one row at a time, so call to get a row.
											$rows = json_decode(file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_table_row&table=' . urlencode(@openssl_encrypt($file, 'aes128', $_POST['communication_key'])) . '&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key']))));
											
											// If there is a row, not an empty value, indicating the file has been imported...
											if($rows->status === true && $rows->data) {
												// Try to decrypt.
												$rows = openssl_decrypt($rows->data, 'aes128', $_POST['communication_key']);
												
												// If the row decrypted...
												if($rows) {
													// Decode the JSON.
													$row = json_decode($rows);
													
													// If we made it this far, we can start importing.
													if($row) {
														
														echo '<script>document.getElementById("migrate-status").innerHTML = "Importing row ' . $row_count . ' of ' . $table . '."</script>';
														flush();
														
														// For options tables, we don't want to replace the migration keys
														// since that would break migration when the decrypt check runs.
														if($table == 'options') {
															if(
																$row[1] == '_transient_launchpad_migration_communication_key' ||
																$row[1] == '_transient_timeout_launchpad_migration_communication_key'
															) {
																break;
															}
														}
														// If we're in the usermeta table, we want to zero-out the session tokens.
														// If you update the session tokens with the current site's tokens,
														// the user has to log in twice because the first attempt causes a 
														// missing expire date key in WP's session variables.  Instead,
														// zeroing-out the record just forces the user to log back in.
														if($table == 'usermeta') {
															if(
																$row[2] == 'session_tokens'
															) {
																$row[3] = '';
															}
														}
														
														// Get the list of columns from the table.
														$columns = $wpdb->get_results('SHOW columns FROM ' . $wpdb->prefix . $table);
														// Placeholder for the query fields.
														$query_fields = '';
														
														// Loop the columns to create the fields to insert.
														foreach($columns as $column) {
															if($query_fields) {
																$query_fields .= ', ';
															}
															$query_fields .= '`' . $column->Field . '` = %s';
														}
														
														// Placeholder for the query.
														$query = false;
														
														// If there's query fields, create the query.
														if($query_fields) {
															$query = 'REPLACE INTO `' . $wpdb->prefix . $table . '` SET ' . $query_fields;
														}
														
														// Loop the row's columns to substitute the domain.
														foreach($row as &$col) {
															// If the row is serialized, unserialize and traverse it.
															if(is_serialized($col)) {
																$tmp_col = unserialize($col);
																$tmp_col = launchpad_migrate_domain_replace($tmp_col, $_POST['migrate_url'], 'http://' .$_SERVER['HTTP_HOST']);
																$col = serialize($tmp_col);
															
															// Otherwise, we want to replace the remote URL with the local.
															} else {
																$col = launchpad_migrate_domain_replace($col, $_POST['migrate_url'], 'http://' . $_SERVER['HTTP_HOST']);
															}
														}
														
														// If there is a query, execute it.
														if($query) {
															$results = $wpdb->query(
																$wpdb->prepare(
																	$query,
																	$row
																)
															);
														}
														
														// If we are migrating files, handle that.
														if(isset($_POST['migrate_attached_files'])) {
															// If we're on the posts table and the current is an attachment,
															// we need to get the remote file.
															if($table == 'posts' && $row[20] === 'attachment') {
																echo '<script>document.getElementById("migrate-status").innerHTML = "Importing file for row ' . $row_count . ' of ' . $table . '."</script>';
																flush();
														
																// Get the file details.
																$file_path = get_attached_file($row[0]);
																
																// Get the path to the full size image.
																$remote_file_path = wp_get_attachment_url($row[0]);
																
																// Prepend the migration URL since that is where this is.
																$remote_file_path = $_POST['migrate_url'] . $remote_file_path;
																
																// Open the local file.
																$f = fopen($file_path, 'w');
																
																// Write the remote contents.
																if($f) {
																	fwrite($f, @file_get_contents($remote_file_path));
																	fclose($f);
																}
																
																// If a file is empty, delete it.
																// It probably means the file was 404.
																if(empty($file_path)) {
																	unlink($file_path);
																}
																
																// Regenerate the thumbnails
																launchpad_do_regenerate_image($row[0]);
															}
														}
													}
												} else {
													$errors[] = 'Could not decrypt a row of data for ' . $table . '.  Please verify the communication key and try to import again.';
													if(count($errors) > 20) {
														$errors[] = 'Quit import due to excessive import errors.  This can indicate that the remote communication key has expired.';
														break 2;
													}
												}
											} else if($rows->status === false) {
												$errors[] = $rows->message;
												if(count($errors) > 20) {
													$errors[] = 'Quit import due to excessive import errors.  This can indicate that the remote communication key has expired.';
													break 2;
												}
											} else {
												$row = false;
											}
										
										// Loop while there are rows.
										} while($row);
									}
									
									// The file is complete, so delete the remote import file.
									$table_unlink = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migrate_get_table_row&table=' . urlencode(@openssl_encrypt($file, 'aes128', $_POST['communication_key'])) . '&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key'])) . '&unlink=true');
									$table_unlink = json_decode($table_unlink);
									if($table_unlink->status === false) {
										$errors[] = $table_unlink->message;
									}
								}
								
								// The import is complete, so clear the remote migration key.
								$key_cleared = file_get_contents($_POST['migrate_url'] . '/api/?action=launchpad_migration_clear_key&communication_test=' . urlencode(@openssl_encrypt($remote_version->nonce, 'aes128', $_POST['communication_key'])));
								
								// Show the database complete message.
								$form = 'database_complete';
								echo '<script>document.getElementById("migrate-status").innerHTML = ""; document.getElementById("migrate-status").style.display="none";</script>';
								flush();
							}
						}
					}
				
				// The remote key is bad, so error.
				} else {
					$errors[] = $remote_key_valid->message;
				}
			break;
		}
	}
	
	// If there is a migration url posted, save it as a transient so it doesn't have to
	// be populated by the user.
	if(isset($_POST['migrate_url'])) {
		set_transient('launchpad_migration_remote_url', $_POST['migrate_url']);
	
	// Otherwise, set the migration URL to the transiet.
	} else {
		$_POST['migrate_url'] = get_transient('launchpad_migration_remote_url');
	}
	
	// If there is a communication key posted, save it for an hour.
	if(isset($_POST['communication_key'])) {
		set_transient('launchpad_migration_remote_communication_key', $_POST['communication_key'], 60 * 60);
	
	// If not, get the transient value for it.
	} else {
		$_POST['communication_key'] = get_transient('launchpad_migration_remote_communication_key');
	}
	
	?>
	<div class="wrap">
		<h2>Database Migration</h2>
		<div class="error"><p><strong>SUPER BETA!</strong>  You probably shouldn't use this in production. Make a backup of your database before you migrate!</p></div>
		<?php

			if($errors) {
				echo '<h3>Errors were encountered!</h3>';
				foreach($errors as $error) {
					echo "<li>$error</li>";
				}
				echo '</ul>';
			} else {
			
		?>
		<p>This tool is meant to be used to migrate data between dev and live sites that are built on Launchpad and installed at the root-level of a domain.  <strong style="color:#dd3d36">Data is replaced, NOT merged, with the domain names swapped out.</strong>  Serialized data should be unserialized before the domain names are replaced, so it should not break metadata and plugins.  That said, this tool is not well tested.  Use at your own risk and, for the love of all that is good, <strong style="color:#dd3d36">make a backup of your database and assets before you pull the trigger!</strong></p>
		<p>If this site is the remote site, this communication key is valid for 10 minutes: <strong><?= $communication_key ?></strong></p>
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
							<fieldset class="launchpad-metabox-fieldset launchpad-checkbox-toggle">
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
					// Delete our transient remote key because it has already been cleared
					// and we will have to get another one.
					delete_transient('launchpad_migration_remote_communication_key');
					
					?>
					<p><strong>The database import is complete<?= $errors ? ', though some errors were encountered' : '' ?>. The remote key has been forcibly expired.  You must get a new key from the remote server.</strong></p>
					<?php
						
				break;
			}
			?>
		</form>
	</div>
	<?php
}