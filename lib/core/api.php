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
 * Force Browser to Download File
 * 
 * You can either do /download/path/to/file.txt or
 * /download/?file=/path/to/file.txt
 *
 * @param		string $file The path or URL to the file.
 * @since		1.0
 */
function launchpad_download_handler($file = false) {
	// If the file is local, only allow downloading types that are allowed
	// for uploading to WordPress.
	$allowed_download_types = implode('|', array_keys(get_allowed_mime_types()));
	
	// If there is no file being passed, use the querystring.
	if(!$file) {
		$file = $_GET['file'];
	}
	
	// This is used as a flag for deciding whether to download or redirect.
	$exec_download = true;
	
	// If this is not 
	if(!preg_match('|^https?://|', $file)) {
		// Remove any ../ to keep people from getting outside of the folder.
		$file = preg_replace('|\.\./|', '', $file);
		
		if(substr($file, 0, 1) !== '/') {
			$file = '/' . $file;
		}
		
		// Make it relative to the root.
		$file = $_SERVER['DOCUMENT_ROOT'] . $file;
		
		// If the modifications turn the path into something that doesn't exist
		// or if the path modifications point to a folder instead of a file,
		// we can't download it, so set the variable to not allow the download.
		if(!file_exists($file) || is_dir($file)) {
			$exec_download = false;
		}
		
		// If it's not an uploadable type, don't allow the download.
		if(!preg_match('/(' . $allowed_download_types . ')$/', $file)) {
			$exec_download = false;
		}
	}
	
	// If we decided to download the file, add headers and read it out.
	if($exec_download) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Connection: Keep-Alive');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		if(file_exists($file)) {
			header('Content-Length: ' . filesize($file));
		}
		
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
