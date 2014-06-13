<?php

/**
 * API
 *
 * Handles the AJAX API calls for the basic functionality of the front end.
 *
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Sample API Call
 *
 * @since		1.0
 */
function launchpad_hello_world() {
	echo json_encode('Hello, World!');
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_hello_world', 'launchpad_hello_world');
	add_action('wp_ajax_nopriv_hello_world', 'launchpad_hello_world');
}


/**
 * Create Download Headers for LOCAL FILES
 *
 * @since		1.0
 */
function launchpad_download_handler($file = false) {
	// If there is no file being passed, use the querystring.
	if(!$file) {
		$file = $_GET['file'];
	}
	
	// Remove http:// because the file must be local.
	$file = preg_replace('|https?://?|', '', $file);
	
	// Remove any ../ to keep people from getting outside of the folder.
	$file = preg_replace('|\.\./|', '', $file);
	
	// Replace the host name.
	$file = str_replace($_SERVER['HTTP_HOST'], '', $file);
	
	if(substr($file, 0, 1) !== '/') {
		$file = '/' . $file;
	}
	
	// Make it relative to the root.
	$file = $_SERVER['DOCUMENT_ROOT'] . $file;
	
	// If the file exists, set the download headers and read it to the browser.
	if(file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		
	// If not, redirect to the file.
	} else {
		header('Location: ' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file));
	}
	
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_download', 'launchpad_download_handler');
	add_action('wp_ajax_nopriv_download', 'launchpad_download_handler');
}
