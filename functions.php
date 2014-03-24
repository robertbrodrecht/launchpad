<?php
/**
 * Functions Includes
 *
 * Include the hard work of the theme.
 * 
 * @package 	Launchpad
 * @since		1.0
 * @todo		Do a second pass on where filtering is supported.
 * @todo		Determine where to trigger custom actions.  http://archive.extralogical.net/2007/06/wphooks/
 */

global $site_options;

$site_options = get_option('launchpad_site_options', '');

/** System functions like theme activation, rewrites, etc. */
include 'functions/system.php';
/** Modifications to the admin area like options pages and admin cleanup. */
include 'functions/admin.php';
/** Security related features like limit login attempts. */
include 'functions/security.php';
/** Post Type related code for registering and creating metaboxes.  */
include 'functions/post-types.php';
/** Code for custom API calls.  */
include 'functions/api.php';
/** Template related modifications such as nav menu registration, header cleanup, page cache, etc.  */
include 'functions/template.php';
/** Cache-related functions.  */
include 'functions/cache.php';
/** Custom functions for handling various duties.  */
include 'functions/utilities.php';

/** YOUR FUNCTIONS GO HERE.  */
if(file_exists('functions-custom.php')) {
	include 'functions-custom.php';	
}