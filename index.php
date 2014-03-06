<?php

/**
 * Base file
 *
 * The majority of the template-deciding work can be done here.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */

if(!isset($_GET['launchpad_ajax'])) {
	get_header();	
}
launchpad_get_template_part('content', get_post_type());
if(!isset($_GET['launchpad_ajax'])) {
	get_footer();
}