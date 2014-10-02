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
function launchpad_do_regenerate_image() {
	check_ajax_referer('launchpad-admin-ajax-request', 'nonce');
	sleep(5);
	
	header('Content-type: application/json');
	echo json_encode(array('attachment_id' => $_GET['attachment_id'], 'status' => 1));
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_do_regenerate_image', 'launchpad_do_regenerate_image');
}