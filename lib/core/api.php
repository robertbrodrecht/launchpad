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
add_action('wp_ajax_hello_world', 'launchpad_hello_world');
add_action('wp_ajax_nopriv_hello_world', 'launchpad_hello_world');

