<?php
/*
Template Name: Sidebar First in Source
*/

/**
 * Left sidebar base file
 *
 * The majority of the template-deciding work can be done here.
 *
 * @package 	Launchpad
 * @since		1.0
 */

add_filter('launchpad_use_sidebar', 'launchpad_force_top_sidebar', 9999);

get_header();
$content_type = launchpad_determine_best_template_file($post);
launchpad_get_template_part('content', $content_type);
get_footer();