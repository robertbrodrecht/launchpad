<?php

/**
 * Base file
 *
 * The majority of the template-deciding work can be done here.
 *
 * @package 	Launchpad
 * @since		1.0
 */

get_header();	
launchpad_get_template_part('content', get_post_type());
get_footer();