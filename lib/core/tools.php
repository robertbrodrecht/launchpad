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
		$decrypt_status = @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key);
		
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
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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
function launchpad_migration_truncate_table() {
	global $wpdb;
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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
	$table = @openssl_decrypt($_POST['table'], 'aes128', $communication_key);
	$prefix = @openssl_decrypt($_POST['requires_prefix'], 'aes128', $communication_key);
	
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
	
	// If the table is falsey, error.
	if(!$table) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Missing table name. No operation executed.'
			)
		);
		exit;
	}
	
	$has_table = $wpdb->get_results('SHOW TABLES LIKE "' . ($prefix ? $wpdb->prefix : '') . $table . '"');
	
	if($has_table) {	
		// Truncate the table.
		$truncate = $wpdb->query('TRUNCATE TABLE ' . ($prefix ? $wpdb->prefix : '') . $table);
	} else {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'Table does not exist.  No truncate required.'
			)
		);
		exit;
	}
	
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
	add_action('wp_ajax_launchpad_migration_truncate_table', 'launchpad_migration_truncate_table');
	add_action('wp_ajax_nopriv_launchpad_migration_truncate_table', 'launchpad_migration_truncate_table');
}


/**
 * Put in a Table's Rows
 * 
 * @since		1.5
 */
function launchpad_migrate_put_file() {
	global $wpdb;
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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
	
	$row = @openssl_decrypt($_POST['row'], 'aes128', $communication_key);
	if($row === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt row.'
			)
		);
		exit;
	}
	
	$file_path = wp_get_attachment_url($row);
	if(!$file_path) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not find the appropriate place to save the file for row ' . $row . '.'
			)
		);
		exit;
	}
	
	// Try to create the path if it doesn't exist.
	if(!file_exists(pathinfo($_SERVER['DOCUMENT_ROOT'] . $file_path, PATHINFO_DIRNAME))) {
		@mkdir(pathinfo($_SERVER['DOCUMENT_ROOT'] . $file_path, PATHINFO_DIRNAME), 0777, true);
	}
	
	if(!file_exists(pathinfo($_SERVER['DOCUMENT_ROOT'] . $file_path, PATHINFO_DIRNAME))) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not create the folder to save the file for row ' . $row . '.'
			)
		);
		exit;
	}
	
	if($_FILES) {
		// Move the file.
		move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $file_path);
	} else {
		
		$file_url = @openssl_decrypt($_POST['file_url'], 'aes128', $communication_key);
		if($file_url === false) {
			echo json_encode(
				array(
					'status' => false,
					'message' => 'Could not decrypt file location.'
				)
			);
			exit;
		}
		
		@copy($file_url, $_SERVER['DOCUMENT_ROOT'] . $file_path);
	}
	
	if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path)) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not write the file for row ' . $row . '.'
			)
		);
		exit;
	}
	
	// Make sure it's writable so we can delete it in FTP if we want.
	chmod($_SERVER['DOCUMENT_ROOT'] . $file_path, 0777);

	// Re-generate the images for the file.
	$regen_results = launchpad_do_regenerate_image($row);
	if(!$regen_results['status']) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'The file for record ' . $row . ' (' . $file_path . ') has been uploaded but thumbnails could not be created.  Try going manually regenerating thumbnails on the target site.'
			)
		);		
	} else {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'The file has been uploaded.'
			)
		);
	}
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_put_file', 'launchpad_migrate_put_file');
	add_action('wp_ajax_nopriv_launchpad_migrate_put_file', 'launchpad_migrate_put_file');
}


/**
 * Put in a Table's Rows
 * 
 * @since		1.5
 */
function launchpad_migrate_put_rows() {
	global $wpdb;
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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
	
	$prefix = @openssl_decrypt($_POST['requires_prefix'], 'aes128', $communication_key);
	
	$table = @openssl_decrypt($_POST['table'], 'aes128', $communication_key);
	if($table === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt table.'
			)
		);
		exit;
	}
	
	$create = @openssl_decrypt($_POST['create'], 'aes128', $communication_key);
	if($create === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt create statement.'
			)
		);
		exit;
	}
	
	$create = json_decode($create, true);
	
	$rows = @openssl_decrypt($_POST['rows'], 'aes128', $communication_key);
	if($rows === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt rows.'
			)
		);
		exit;
	}
	
	$rows = json_decode($rows, true);
	$success = 0;
	$fail = 0;
	
	$table_with_prefix = $table;
	if($prefix) {
		$table_with_prefix = $wpdb->prefix . $table_with_prefix;
	}
	
	$has_table = $wpdb->get_results('SHOW TABLES LIKE "' . $table_with_prefix . '"');
	if(!$has_table) {
		$create_query = $create['Create Table'];
		$create_query = str_replace('`' . $create['Table'] . '`', '`' . $table_with_prefix . '`', $create_query);
		
		$create_results = $wpdb->query($create_query);
		if(!$create_results) {
			echo json_encode(
				array(
					'status' => false,
					'message' => 'Table ' . $table . ' does not exist and could not be created.'
				)
			);
			exit;
		}
	}
	
	// Prefix the table so it is easier to work with.
	$local_table = $table_with_prefix;
	
	// Get the table's columns to build the query.
	$columns = $wpdb->get_results('SHOW columns FROM ' . $local_table);
	
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
		$query = 'REPLACE INTO `' . $local_table . '` SET ' . $query_fields;
	}
	
	$errors = array();
	
	foreach($rows as $row) {
		$has_error = false;
		
		// If there is a query, execute the query.
		if($query) {
			$results = $wpdb->query(
				$wpdb->prepare(
					$query,
					$row
				)
			);
			
			if(!$results) {
				$errors[] = @$row[0];
				$has_error = true;
				$fail++;
			} else {
				$success++;
			}
		}
	}
	
	if($errors) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not update ' . count($errors) . ' rows on ' . $table . '.',
				'data' => array(
					'success' => $success,
					'fail' => $fail
				)
			)
		);
	} else {
		echo json_encode(
			array(
				'status' => true,
				'message' => 'All rows updated.',
				'data' => array(
					'success' => $success,
					'fail' => $fail
				)
			)
		);
	}
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_put_rows', 'launchpad_migrate_put_rows');
	add_action('wp_ajax_nopriv_launchpad_migrate_put_rows', 'launchpad_migrate_put_rows');
}


/**
 * Get a Table's Rows
 * 
 * @since		1.5
 */
function launchpad_migrate_get_rows() {
	global $wpdb;
	
	$rows_per_page = 1000;
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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

	$prefix = @openssl_decrypt($_POST['requires_prefix'], 'aes128', $communication_key);
	
	$table = @openssl_decrypt($_POST['table'], 'aes128', $communication_key);
	if($table === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt table.'
			)
		);
		exit;
	}
	
	$offset = @openssl_decrypt($_POST['offset'], 'aes128', $communication_key);
	if($offset === false) {
		echo json_encode(
			array(
				'status' => false,
				'message' => 'Could not decrypt offset.'
			)
		);
		exit;
	}
	
	$offset = (int) $offset;
	
	$results = $wpdb->get_results('SELECT * FROM `' . ($prefix ? $wpdb->prefix : '') . $table . '` LIMIT ' . ($offset * $rows_per_page) . ', ' . $rows_per_page);
	
	echo json_encode(
		array(
			'status' => true,
			'message' => 'Rows attached.',
			'data' => @openssl_encrypt(json_encode($results), 'aes128', $communication_key)
		)
	);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_get_rows', 'launchpad_migrate_get_rows');
	add_action('wp_ajax_nopriv_launchpad_migrate_get_rows', 'launchpad_migrate_get_rows');
}


/**
 * Get a Table List
 * 
 * @since		1.5
 */
function launchpad_migrate_get_tables() {
	global $wpdb;
	
	// Get the communication key.
	$communication_key = get_transient('launchpad_migration_communication_key');
	
	// Get the migration nonce.
	$nonce = wp_create_nonce('migration');
	
	// Try to decrypt the key.  If it fails or there is no key, just quit there.
	if(!$communication_key || @openssl_decrypt($_POST['communication_message'], 'aes128', $communication_key) !== $nonce) {
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
	
	$return = array(
		'status' => true,
		'message' => 'Table list is attached.',
		'data' => array()
	);
	
	$table_list = $wpdb->get_results('SHOW TABLES');
	foreach($table_list as $table) {
		$table = (array) $table;
		$table = array_pop($table);
		$requires_prefix = preg_match('/^' . $wpdb->prefix . '/', $table) ? true : false;
		$table_base_name = preg_replace('/^' . $wpdb->prefix . '/', '', $table);
		
		$total_rows = $wpdb->get_results('SELECT count(*) as total FROM `' . $table . '`');
		$total_rows = array_pop($total_rows);
		$total_rows = $total_rows->total;
		
		$total_files = 0;
		if($table_base_name === 'posts') {
			$total_files = $wpdb->get_results('SELECT count(*) as total FROM `' . $table . '` WHERE `post_type` = "attachment"');
			$total_files = array_pop($total_files);
			$total_files = $total_files->total;
		}
		
		$create_table = $wpdb->get_results('SHOW CREATE TABLE `' . $table . '`');
		$create_table = array_pop($create_table);
		
		$return['data'][$table] = array(
			'table' => $table,
			'table_base' => $table_base_name,
			'requires_prefix' => $requires_prefix,
			'rows' => $total_rows,
			'files' => $total_files,
			'create' => $create_table
		);
	}
	echo json_encode($return);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_launchpad_migrate_get_tables', 'launchpad_migrate_get_tables');
	add_action('wp_ajax_nopriv_launchpad_migrate_get_tables', 'launchpad_migrate_get_tables');
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
 * Handle a Migration API Call
 * 
 * @param		string $url The URL to send the API request to.
 * @param		string $action The API action to do.
 * @param		string $communication_key The communication key from the remote server.
 * @param		string $communication_message The nonce from the remote server.
 * @param		array $data The data to send to the server.
 * @param		array $files The files to send to the server.
 * @since		1.5
 */
function launchpad_migrate_api_call($url = false, $action = false, $communication_key = false, $communication_message = false, $data = array(), $files = array()) {
	if(!$url) {
		return (object) array(
			'status' => false,
			'message' => 'No host URL was specified.'
		);
	}
	if(!$action) {
		return (object) array(
			'status' => false,
			'message' => 'No action was specified.'
		);
	}
	if(!$communication_key) {
		return (object) array(
			'status' => false,
			'message' => 'No communication key was specified.'
		);
	}
	if(!$communication_message) {
		return (object) array(
			'status' => false,
			'message' => 'No nonce was specified.'
		);
	}
	if(!is_array($data)) {
		if($data) {
			$data = array($data);
		} else {
			$data = array();
		}
	}
	if(!is_array($files)) {
		if($files) {
			$files = array('file' => $files);
		} else {
			$files = array();
		}
	}
	
	$postdata = array(
		'action' => $action,
		'communication_message' => @openssl_encrypt($communication_message, 'aes128', $communication_key)
	);

	if($data) {
		foreach($data as $data_key => $data_value) {
			if(is_array($data_value) || is_object($data_value)) {
				$data_value = json_encode($data_value);
			}
			$data[$data_key] = @openssl_encrypt($data_value, 'aes128', $communication_key);
		}
	}
	
	if($files) {
		foreach($files as $file_key => $file_path) {
			if(class_exists('CurlFile')) {
				$postdata[$file_key] = new CurlFile($file_path);
			} else {
				$postdata[$file_key] = '@' . $file_path;
			}
		}
	}
	
	$postdata = array_merge($postdata, $data);
	
	if(substr($url, -1) !== '/') {
		$url = $url . '/';
	}
	
	if(!preg_match('|^https?://|', $url)) {
		$url = 'http://' . $url;
	}
	
	// initialise the curl request
	$request = curl_init($url . 'api/');
	
	// send a file
	curl_setopt($request, CURLOPT_POST, true);
	curl_setopt($request, CURLOPT_POSTFIELDS, $postdata);
	
	// output the response
	curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($request);
	
	// close the session
	curl_close($request);
	
	$result_decode = json_decode($result);
	
	if($result_decode === false) {
		return (object) array(
			'status' => false,
			'message' => 'The API response could not be decoded: ' . $result
		);
	} else {
		return $result_decode;
	}
}


/**
 * Add an admin page for migration.
 *
 * @since		1.5
 */
function launchpad_migrate_add_admin_page() {
	add_submenu_page('tools.php', 'Migrate', 'Migrate', 'update_core', 'launchpad/migrate/', 'launchpad_migrate_render_admin_page', 99);
}
if(is_admin()) {
	add_action('admin_menu', 'launchpad_migrate_add_admin_page');
}


/**
 * Validate Local to Remote
 *
 * @since		1.5
 */
function launchpad_migrate_validate_credentials($remote_url = false, $communication_key = false) {
	$return = array(
		'status' => true,
		'message' => 'All credentials valid.',
		'local' => array(
			'version' => false,
			'nonce' => false,
			'max_upload' => false
		),
		'remote' => array(
			'version' => false,
			'nonce' => false,
			'max_upload' => false
		)
	);
	
	if(!$remote_url) {
		$return['status'] = false;
		$return['message'] = 'No remote URL provided.';
		
		return (object) $return;
	}
	
	if(!$communication_key) {
		$return['status'] = false;
		$return['message'] = 'No remote communication key provided.';
		
		return (object) $return;
	}
	
	$local = launchpad_migrate_api_call(
		$_SERVER['HTTP_HOST'], 
		'launchpad_version', 
		get_transient('launchpad_migration_communication_key'),
		'initialize'
	);
	
	if($local->status === false) {
		return $local;
	} else {
		$return['local'] = $local->data;
	}
	
	$remote = launchpad_migrate_api_call(
		$remote_url, 
		'launchpad_version', 
		$communication_key,
		'initialize'
	);
	
	if(!isset($remote->data->nonce)) {
		return (object) array(
			'status' => false,
			'message' => 'Could not validate with remote server.  The communication key may have expired.  Please verify the communication key and try again.'
		);
	} else {
		$return['remote'] = $remote->data;
	}
	
	$return['local']->url = 'http://' . $_SERVER['HTTP_HOST'] . '/';
	$return['local']->communication_key = get_transient('launchpad_migration_communication_key');	
	$return['remote']->url = $remote_url;
	$return['remote']->communication_key = $communication_key;
	
	$return = (object) $return;
	
	if($return->remote->version != $return->local->version) {
		$return->status = false;
		$return->message = 'Remote Launchpad version is ' . $return->remote->version . '. Local Launchpad version is ' . $return->local->version . '. Both sites must be running the same version of Launchpad.';
	}
	
	return $return;
}


/**
 * Logic to Copy from Local to Remote
 * 
 * @param		object $local Details about local.
 * @param		object $remote Details about remote.
 * @param		array $tables The table data to migrate.
 * @param		bool $show_updates Whether to use direct output to show progress,
 * @since		1.5
 * @uses		launchpad_migrate_domain_replace
 */
function launchpad_migrate_handle_import($local_server = false, $remote_server = false, $tables = array(), $show_updates = false) {
	if(!$remote_server || !$local_server) {
		return (object) array(
			'status' => false,
			'message' => 'Missing local and/or remote details.'
		);
	}
	
	if($show_updates) {
		echo '<div id="migrate-status" class="wrap"><strong><img src="/wp-includes/images/wpspin-2x.gif" width="16" height="16"></strong><br>Preparing for Migration.</div>';
		flush();
	}
					
	$table_list_results = launchpad_migrate_api_call(
		$local_server->url, 
		'launchpad_migrate_get_tables', 
		$local_server->communication_key, 
		$local_server->nonce
	);
	
	$errors = array();
	$files = array();
	$success = 0;
	$fail = 0;
	
	if($table_list_results->status == false) {
		$errors[] = $table_list_results->message;
	} else {
		$migrate_attached_files = isset($_POST['migrate_attached_files']);
		
		$actions_complete = 0;
		$total_actions = 0;
		$total_records = 0;
		$total_files = 0;
		foreach($table_list_results->data as $table => $details) {
			if(isset($tables[$table])) {
				$total_actions += $details->rows;
				$total_records += $details->rows;
				
				//if($migrate_attached_files) {
				//	$total_actions += $details->files;
				//	$total_files += $details->files;
				//}
			}
		}
		
		foreach($tables as $table => $details) {
			$details = $table_list_results->data->$table;
			$rows_offset = 0;
			$current_row = 1;
			
			// If the table is not the options table, send a request to truncate.
			// We don't truncate options because it might cause the site to 
			// freak out if someone hits a page during import.
			if($details->table_base != 'options') {
						
				$truncate_results = launchpad_migrate_api_call(
					$remote_server->url, 
					'launchpad_migration_truncate_table', 
					$remote_server->communication_key,
					$remote_server->nonce,
					array(
						'table' => $details->table_base,
						'requires_prefix' => $details->requires_prefix,
					)
				);
				
				if($truncate_results->status === false) {
					$errors[] = $truncate_results->message;
				}
			}
			
			do {
				if($show_updates) {
					$complete = floor($actions_complete/$total_actions*100);
					$rows_offset_human = $rows_offset+1;
					echo "<script>document.getElementById('migrate-status').innerHTML = '<strong>$complete%</strong><br>Getting page {$rows_offset_human} of {$details->table}';</script>";
					flush();
				}
				
				$table_has_rows = true;
				$table_rows_results = launchpad_migrate_api_call(
					$local_server->url, 
					'launchpad_migrate_get_rows', 
					$local_server->communication_key,
					$local_server->nonce,
					array(
						'table' => $details->table_base,
						'requires_prefix' => $details->requires_prefix,
						'offset' => $rows_offset
					)
				);
				
				$rows_offset++;
				
				if($table_rows_results->status == false) {
					$table_has_rows = false;
					$errors[] = $table_rows_results->message;
				} else {
					$table_has_rows = @openssl_decrypt(
						$table_rows_results->data, 
						'aes128', 
						$local_server->communication_key
					);
					
					$rows_in_table = $details->rows;
					
					if($table_has_rows !== false && trim($table_has_rows)) {
						$table_has_rows = json_decode($table_has_rows, true);
						$table_has_rows_array = array_values($table_has_rows);
						
						if($table_has_rows) {
							foreach($table_has_rows as $row_counter => $row) {
								if($show_updates && $actions_complete%10 === 0) {
									$complete = floor($actions_complete/$total_actions*100);
									$current_row_sum = $current_row + $rows_offset;
									echo "<script>document.getElementById('migrate-status').innerHTML = '<strong>$complete%</strong><br>Processing row {$current_row_sum} of {$rows_in_table} in {$details->table}';</script>";
									flush();
								}
								
								// For options tables, we don't want to replace the migration keys
								// since that would break migration when the decrypt check runs.
								if($details->table_base == 'options') {
									if(
										preg_match('/^_transient/', $row['option_name'])
									) {
										unset($table_has_rows[$row_counter], $table_has_rows_array[$row_counter]);
										$total_records--;
									}
								}
								
								// If we're in the usermeta table, we want to zero-out the session tokens.
								// If you update the session tokens with the current site's tokens,
								// the user has to log in twice because the first attempt causes a 
								// missing expire date key in WP's session variables.  Instead,
								// zeroing-out the record just forces the user to log back in.
								if($details->table_base == 'usermeta') {
									if(
										$row['meta_key'] == 'session_tokens'
									) {
										$row['meta_key'] = '';
										$row[3] = '';
									}
								}
								
								// Figure out how to hanle attachments.
								if($details->table_base === 'posts' && $row['post_type'] === 'attachment') {
									$files[] = $row;
								}
								
								$actions_complete++;
								$current_row++;
							}
							
							$table_has_rows = launchpad_migrate_domain_replace(
								$table_has_rows, 
								$local_server->url, 
								$remote_server->url
							);
							
							$table_put_results = launchpad_migrate_api_call(
								$remote_server->url, 
								'launchpad_migrate_put_rows', 
								$remote_server->communication_key,
								$remote_server->nonce,
								array(
									'table' => $details->table_base,
									'requires_prefix' => $details->requires_prefix,
									'create' => $details->create,
									'rows' => $table_has_rows
								)
							);
							
							if(isset($table_put_results->data->success)) {
								$success += $table_put_results->data->success;
								$fail += $table_put_results->data->fail;
							} else {
								$fail += count($table_has_rows);
							}
							
							if($table_put_results->status == false) {
								$errors[] = $table_put_results->message;
							}
						}
					}
				}
			} while($table_has_rows);
		}
	}
	
	if($errors) {
		return (object) array(
			'status' => false,
			'message' => $errors,
			'data' => (object) array(
				'files' => $files,
				'success' => $success,
				'fail' => $fail,
				'total' => $total_records
			)
		);
	}
					
	return (object) array(
		'status' => true,
		'message' => 'Database import completed successfully.',
		'data' => (object) array(
			'files' => $files,
			'success' => $success,
			'fail' => $fail,
			'total' => $total_records
		)
	);
}


/**
 * Logic to Copy Files from Local to Remote
 * 
 * @param		object $local Details about local.
 * @param		object $remote Details about remote.
 * @param		bool $local_files Whether the files are local or remote.
 * @param		bool $show_updates Whether to use direct output to show progress,
 * @param		string $direction "push" or "pull"
 * @param		array $records The records that we need to handle files for.
 * @since		1.5
 * @uses		launchpad_migrate_domain_replace
 */
function launchpad_migrate_handle_import_files($local_server = false, $remote_server = false, $local_files = true, $records = array(), $show_updates = false) {
	
	if(!$remote_server || !$local_server || !$records) {
		return (object) array(
			'status' => false,
			'message' => 'Missing local and/or remote details and/or records to handle.'
		);
	}
	
	$actions_complete = 0;
	$total_actions = count($records);
	$success = 0;
	$fail = 0;
	$errors = array();
	
	foreach($records as $cnt => $row) {
		if($show_updates) {
			$complete = floor($actions_complete/$total_actions*100);
			echo "<script>document.getElementById('migrate-status').innerHTML = '<strong>$complete%</strong><br>Copying file " . ($cnt+1) . " of " . count($records) . " for record {$row['ID']}';</script>";
			flush();
		}
		$table_put_results = false;
		$actions_complete++;
		if($local_files) {
			$file_path = wp_get_attachment_url($row['ID']);
			if(!$file_path || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path)) {
				$errors[] = 'Could not upload ' . $file_path . ' because the file is missing.';
			} else if(filesize($_SERVER['DOCUMENT_ROOT'] . $file_path) > $remote_server->max_upload) {
				$errors[] = 'Could not upload ' . $file_path . ' because the file is too big.';
			} else {
				$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;
			}
			$table_put_results = launchpad_migrate_api_call(
				$remote_server->url, 
				'launchpad_migrate_put_file', 
				$remote_server->communication_key,
				$remote_server->nonce,
				array(
					'row' => $row['ID']
				),
				array(
					'file' => $file_path
				)
			);
		} else {
			$file_path = wp_get_attachment_url($row['ID']);
			$table_put_results = launchpad_migrate_api_call(
				$remote_server->url, 
				'launchpad_migrate_put_file', 
				$remote_server->communication_key,
				$remote_server->nonce,
				array(
					'row' => $row['ID'],
					'file_url' => $local_server->url . $file_path
				)
			);
		}
		
		if($table_put_results) {
			if(!$table_put_results->status) {
				$fail++;
				$errors[] = $table_put_results->message;
			} else {
				$success++;
			}
		}
	}
	
	return (object) array(
		'status' => empty($errors),
		'message' => (
			empty($errors) ? 
			'All files copied.' : 
			count($errors) . ' files could not be copied: ' . implode(', ', $errors)
		),
		'data' => (object) array(
			'success' => $success, 
			'fail' => $fail, 
			'total' => $total_actions
		)
	);
}


/**
 * Display the Admin Migration Page
 * 
 * @since		1.5
 * @uses		launchpad_migrate_domain_replace
 */
function launchpad_migrate_render_admin_page() {
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
				
				// Validate credentials.
				$validation_results = launchpad_migrate_validate_credentials(
					$_POST['migrate_url'],
					$_POST['communication_key']
				);
				
				if($validation_results->status == false) {
					$errors[] = $validation_results->message;
				} else {
					// Depending on which direction we're going, get the correct table list.
					switch($_POST['migrate_direction']) {
						case 'pull':
							$remote_server = $validation_results->local;
							$local_server = $validation_results->remote;
						break;
						case 'push':
							$remote_server = $validation_results->remote;
							$local_server = $validation_results->local;
						break;
					}
					
					$table_list_results = launchpad_migrate_api_call(
						$local_server->url, 
						'launchpad_migrate_get_tables', 
						$local_server->communication_key, 
						$local_server->nonce
					);
					
					if($table_list_results->status == false) {
						$errors[] = $table_list_results->message;
					} else {
						$table_list = $table_list_results->data;
					}
				}
				
				// If there are not errors, show the options form.
				if(!$errors) {
					$form = 'options';
				}
			break;
			
			// We need to migrate data from one site to the other.
			case 'migrate':
				
				$results = array(
					'tables' => 0,
					'records' => 0,
					'success_records' => 0,
					'fail_records' => 0,
					'files' => 0,
					'success_files' => 0,
					'fail_files' => 0,
				);
				
				$validation_results = launchpad_migrate_validate_credentials(
					$_POST['migrate_url'],
					$_POST['communication_key']
				);
				
				if(!$_POST['migrate_database']) {
					$_POST['migrate_database'] = array();
				}
				
				if($validation_results->status == false) {
					$errors[] = $validation_results->message;
				} else {
					switch($_POST['migrate_direction']) {
						case 'pull':
							$remote_server = $validation_results->local;
							$local_server = $validation_results->remote;
						break;
						case 'push':
							$remote_server = $validation_results->remote;
							$local_server = $validation_results->local;
						break;
					}
					
					$migrate_results = launchpad_migrate_handle_import($local_server, $remote_server, $_POST['migrate_database'], true);
					
					if(!$migrate_results->status) {
						$errors[] = $migrate_results->message;
					}
					
					$results['tables'] = count($_POST['migrate_database']);
					$results['records'] = $migrate_results->data->total;
					$results['success_records'] = $migrate_results->data->success;
					$results['fail_records'] = $migrate_results->data->fail;
					
					if(isset($_POST['migrate_attached_files']) && $migrate_results->data->files) {
						$migrate_file_results = launchpad_migrate_handle_import_files(
							$local_server, 
							$remote_server, 
							($_POST['migrate_direction'] == 'push'),
							$migrate_results->data->files,
							true
						);
						
						if(isset($migrate_file_results->data->total)) {
							$results['files'] = $migrate_file_results->data->total;
							$results['success_files'] = $migrate_file_results->data->success;
							$results['fail_files'] = $migrate_file_results->data->fail;
						} else {
							$results['files'] = count($migrate_results->data->files);
							$results['fail_files'] = count($migrate_results->data->files);
						}
						if(!$migrate_file_results->status) {
							$errors[] = $migrate_file_results->message;
						}
					}
				}
				
				
				// Since we're done with the import, clear the migration key.
				// That avoids any chance of the key sticking around too long.
				$clear_key_results = launchpad_migrate_api_call(
					$remote_server->url, 
					'launchpad_migration_clear_key', 
					$remote_server->communication_key,
					$remote_server->nonce
				);
				
				$form = 'complete';
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
	<script>
		if(document.getElementById('migrate-status')) {
			document.getElementById('migrate-status').style.display = 'none';
		}
	</script>
	<div class="wrap">
		<h2>Database Migration</h2>
		<?php

			if($errors) {
				echo '<h3>Errors were encountered!</h3>';
				foreach($errors as $error) {
					echo "<li>$error</li>";
				}
				echo '</ul>';
			} else {
			
			if($form != 'complete') {
				
		?>
		<p><strong>THIS IS ALPHA SOFTWARE!!! USE AT YOUR OWN RISK.</strong> This tool is meant to be used to migrate data between dev and live sites that are built on Launchpad and installed at the root-level of a domain.  <strong style="color:#dd3d36">Data is replaced, NOT merged, with the domain names swapped out.</strong>  Serialized data should be unserialized before the domain names are replaced, so it should not break metadata and plugins.  That said, this tool is not well tested.  Use at your own risk and, for the love of all that is good, <strong style="color:#dd3d36">make a backup of your database and assets before you pull the trigger!</strong></p>
		<?php 
			
			}
		} 
		
		?>
		<form method="post" id="poststuff">
			<?php
			
			switch($form) {
				default:
					?>
					<div class="postbox">
						<h3 class="hndle"><span>Communication Key</span></h3>
						<div class="inside">
							<div class="launchpad-metabox-field">
								<label>
									If this site is the remote site, this communication key:
									<input type="text" value="<?= $communication_key ?>" readonly="readonly">
									<small>Valid for 10 minutes.</small>
								</label>
							</div>
						</div>	
					</div>
					<div class="postbox">
						<h3 class="hndle"><span>Migration Setup</span></h3>
						<div class="inside">
							<div class="launchpad-metabox-field">
								<label>
									Full URL to Remote Site
									<input type="text" name="migrate_url" value="<?= $_POST['migrate_url'] ?>" placeholder="http://domain.com/">
								</label>
							</div>
							<div class="launchpad-metabox-field">
								<label>
									Remote Site's Communication Key
									<input type="text" name="communication_key" value="<?= $_POST['communication_key'] ?>">
								</label>
							</div>
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
								<legend>Options</legend>
								<div class="launchpad-metabox-field">
									<label>
										<input type="checkbox" name="migrate_attached_files" value="yes"<?= !isset($_POST['migrate_attached_files']) || $_POST['migrate_attached_files'] == 'yes' ? ' checked="checked"' : '' ?>>
										Update Attached Files (Slower)
									</label>
								</div>
							</fieldset>
							<fieldset id="migrate-table-checkbox" class="launchpad-metabox-fieldset launchpad-checkbox-toggle">
								<legend>Tables to Replace</legend>
								<?php
								
								$rows_total = 0;
								$files_total = 0;
								
								foreach($table_list as $table => $details) {
									
									$rows_total += $details->rows;
									$files_total += $details->files;
									
									?>
									<div class="launchpad-metabox-field">
										<label>
											<input type="checkbox" name="migrate_database[<?= $table ?>]" value="<?= htmlentities(json_encode($details)) ?>"<?= !isset($_POST['migrate_database']) || $_POST['migrate_database'][$table] ? ' checked="checked"' : '' ?> data-rows="<?= $details->rows ?>" data-files="<?= $details->files ?>">
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
						Total Rows: <span id="migrate-rows-total"><?= $rows_total ?></span><br>
						Total Files: <span id="migrate-files-total"><?= $files_total ?></span><br><br>
					</div>
					<div>
						<input type="hidden" name="migrate_action" value="migrate">
						<input type="hidden" name="migrate_url" value="<?= $_POST['migrate_url'] ?>">
						<input type="hidden" name="communication_key" value="<?= $_POST['communication_key'] ?>">
						<input type="hidden" name="migrate_direction" value="<?= $_POST['migrate_direction'] ?>">
						<input type="submit" class="button button-primary button-large" value="Start Migration">
					</div>
					<?php
						
						
				break;
				case 'complete':
					// Delete our transient remote key because it has already been cleared
					// and we will have to get another one.
					delete_transient('launchpad_migration_remote_communication_key');
					
					?>
					<div class="updated"><p>The database import is complete<?= $errors ? ', though some errors were encountered' : '' ?>. The remote key has been forcibly expired.  You must get a new key from the remote server.</p></div>
					<div class="postbox">
						<h3 class="hndle"><span>Migration Complete</span></h3>
						<div class="inside">
							<p>Your migration is complete.</p>
							<dl class="launchpad-inline-listing launchpad-statistics-list">
								<dt>Tables Migrated</dt>
								<dd><?= $results['tables'] ?></dd>
								<dt>Records Migrated</dt>
								<dd><?= $results['success_records'] ?> of <?= $results['records'] ?></dd>
								<dt>Files Migrated</dt>
								<dd><?= $results['success_files'] ?> of <?= $results['files'] ?></dd>
							</dl>
						</div>
					</div>
					<?php
						
				break;
			}
			?>
		</form>
	</div>
	<?php
}


/**
 * Return the singular or plural version based on the count.
 * 
 * @param		number|array $count The number of things.
 * @param		string $single The singular version of the string.
 * @param		string $plural The plural version of the string.
 * @returns		string
 * @since		1.6
 * @uses		launchpad_migrate_domain_replace
 */
function plural($count = 0, $single = '', $plural = false) {
	if(is_array($count)) {
		$count = count($count);
	}
	if($count == 1) {
		return $single;
	} else {
		return $plural === false ? $single . 's' : $plural;
	}
}