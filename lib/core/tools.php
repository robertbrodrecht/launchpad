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