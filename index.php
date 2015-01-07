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

// The post type is the default second parameter.
$content_type = get_post_type();
$content_format = get_post_format();

// If the content is singular, such as a page or single post type, handle accordingly.
if(is_singular()) {
	// First, see if there is a content-slug.php
	if(locate_template('content-' . $post->post_name . '.php')) {
		$content_type = $post->post_name;
		
	// Next, if this is a page, see if there is a content-page-slug.php.
	} else if(is_page() && locate_template('content-page-' . $post->post_name . '.php')) {
		$content_type = 'page-' . $post->post_name;
		
	// Next, if this is a single post type with a content format, see if there is a content-posttype-format.php
	} else if(is_single() && $content_format && locate_template('content-' . $content_type . '-' . $content_format . '.php')) {
		$content_type = $content_type . '-' . $content_format;
		
	// Next, if this is a single post type with a content format, try content-format.php.
	} else if(is_single() && $content_format && locate_template('content-' . $content_format . '.php')) {
		$content_type = $content_format;
	
	// Next, if this is a single post type, try content-single-posttype.php.
	} else if(is_single() && locate_template('content-single-' . $content_type . '.php')) {
		$content_type = 'single-' . $content_type;
	
	// Finally, try content-single.php.
	} else if(is_single() && locate_template('content-single.php')) {
		$content_type = 'single';
	}

// If we're on an archive of blog listing page, handle accordingly.
} else if(is_archive() || is_home()) {
	
	// Get the current queried object to help decide how to handle the template.
	$queried_object = get_queried_object();
	
	// If there is a queried object, get specific.
	if($queried_object) {
		
		// If this is a taxonomy page.
		if(isset($queried_object->taxonomy)) {
			// This is the taxonomy name, not the taxonomy rewrite slug!
			$queried_taxonomy = $queried_object->taxonomy;
			// This is the term's slug.
			$queried_term = $queried_object->slug;
			
			// First, try content-tax-termslug.php.
			if(locate_template('content-' . $queried_taxonomy . '-' . $queried_term . '.php')) {
				$content_type = $queried_taxonomy . '-' . $queried_term;
			
			// Next, try content-tax.php.
			} else if(locate_template('content-' . $queried_taxonomy . '.php')) {
				$content_type = $queried_taxonomy;
				
			// Next, try content-archive-posttype.php
			} else if(locate_template('content-archive-' . $content_type . '.php')) {
				$content_type = 'archive-' . $content_type;
			
			// Finally, try content-archive.php.
			} else if(locate_template('content-archive.php')) {
				$content_type = 'archive';
			}
		
		// If it isn't a taxonomy, look for content-archive-posttype.php.
		} else if(locate_template('content-archive-' . $content_type . '.php')) {
			$content_type = 'archive-' . $content_type;
		
		// Finally, try content-archive.php.
		} else if(locate_template('content-archive.php')) {
			$content_type = 'archive';
		}
	
	// If there is no queried object, look for content-archive-posttype.php.
	} else if(locate_template('content-archive-' . $content_type . '.php')) {
		$content_type = 'archive-' . $content_type;
	
	// Finally, try content-archive.php.
	} else if(locate_template('content-archive.php')) {
		$content_type = 'archive';
	}


// Finally, if we're on a search page, use search as the second parameter.
} else if(is_search()) {
	$content_type = 'search';
}

launchpad_get_template_part('content', $content_type);
get_footer();