<?php

/**
 * Template Features
 *
 * Additional template tags and tweaks that affect the general front end of WordPress
 *
 * @package 	Launchpad
 * @since		1.0
 */


// As a precaution, trigger a filter to put Gravity Forms code in footer below the jQuery include.
add_filter('gform_init_scripts_footer', '__return_true');
// Gravity forms tabindex is often harmful.  Turn it off.
add_filter('gform_tabindex', create_function('', 'return false;'));

// Remove capital_P_dangit.
remove_filter('the_title', 'capital_P_dangit', 11);
remove_filter('the_content', 'capital_P_dangit', 11);
remove_filter('comment_text', 'capital_P_dangit', 31);


/**
 * Setup of basic theme support
 *
 * @since		1.0
 */
function launchpad_theme_setup() {
	// By default, include places for headers and footers.
	$menus = array(
		'primary' => 'Primary Navigation',
		'footer' => 'Footer Navigation',
	);
	
	// Apply filters to allow user-removal before registration.
	$menus = apply_filters('launchpad_nav_menus', $menus);
	
	// If we have any menus, register them.
	if($menus) {
		register_nav_menus($menus);
	}
	
	// Post formats.  None by default because... what are they good for?
	$formats = array();
	
	// Apply filters to allow the developer to change it if they find something they are good for.
	$formats = apply_filters('launchpad_post_formats', $formats);
	
	// Add support.
	if($formats) {
		add_theme_support('post-formats', $formats);
	}
	
	// Support for HTML5 and Post Thumbnails.
	add_theme_support('html5', array('comment-list', 'comment-form', 'search-form'));
	add_theme_support('post-thumbnails');
	
	// Create the editor style.
	add_editor_style('css/editor-style.css');
}
add_action('after_setup_theme', 'launchpad_theme_setup');


/**
 * Setup of basic theme support
 *
 * @since		1.0
 * @link		https://developers.facebook.com/docs/opengraph/howtos/maximizing-distribution-media-content/
 */
function launchpad_image_setup() {
	// Support for a hand full of helpful image sizes.
	$image_sizes = array(
		array('XL', '1200', '1200', false),
		array('L', '1000', '1000', false),
		array('M', '800', '800', false),
		array('S', '600', '600', false),
		array('XS', '400', '400', false),
	);
	
	// Allow for adding or removing those.
	$image_sizes = apply_filters('launchpad_image_sizes', $image_sizes);
	
	// Add support for opengraph image size and a gallery thumbnail.
	add_image_size('opengraph', 1200, 1600, false);
	add_image_size('gallery', 300, 300, false);
	
	// Add each of the image sizes that were left after the filters.
	if($image_sizes) {
		foreach($image_sizes as $image_size) {
			add_image_size($image_size[0], $image_size[1], $image_size[2], $image_size[3]);
		}
	}
}
add_action('after_setup_theme', 'launchpad_image_setup');


/**
 * Create the page title
 *
 * @param		$echo bool Whether or not to echo the title
 * @since		1.0
 */
function launchpad_title($echo = false) {
	global $post, $page, $paged, $wp_query;
	
	// If we're on a single page, try to get the SEO'd title.
	if($wp_query->is_single || $wp_query->is_singular) {
		
		// If so, get the SEO info for the post.
		$seo = get_post_meta($post->ID, 'SEO', true);
		
		// If there is a non-empty title set, use the title as-is.  
		// If not, just fall through to the rest.
		if(isset($seo['title']) && trim($seo['title'])) {
			$title = trim($seo['title']);
			
			if($echo) {
				echo $title;
			}
			return $title;
		}
	}
	
	// Set an empty title.
	$title = '';
	
	// Add the WordPress page title.
	$title .= wp_title('', false);
	
	// If we're on a multi-page listing, add the page number.
	if ($paged >= 2 || $page >= 2) {
		$title .= ' (Page ' . max($paged, $page) . ')';
	}
	
	// If there is a title (i.e. not-the-home-page), add a pipe and branding.
	$title = ($title != '' ? $title . ' | ' : '');
	$title .= get_bloginfo('name') . ': ' . get_bloginfo('description', 'display');
	
	// Apply filters to allow the developer to change it.
	$title = apply_filters('launchpad_title', $title);
	
	$title = trim($title);
	
	if($echo) {
		echo $title;
	}
	return $title;
}


/**
 * Create an Excerpt
 * 
 * Generate an excerpt of a certain length, unless the more tag was used to customize the excerpt or the
 * user has set an explicit excerpt.
 *
 * @param		$max_words int The number of words to use if we generate from content based on space characters
 * @param		$echo bool Whether or not to echo the excerpt
 * @param		$id int The Post to use.  Default is the current post.
 * @since		1.0
 */
function launchpad_excerpt($max_words = 32, $echo = false, $id = false) {
	global $post;
	
	// If an ID was passed, get the individual post.
	if($id !== false && (int) $id) {
		$tmp_post = get_post((int) $id);
	}
	
	// If there was no post based on the ID, use the global $post.
	if(!$tmp_post) {
		$tmp_post = $post;
	}
	
	// Normalize the max words.
	$max_words = (int) $max_words;
	
	// If the user made an excerpt, use it.
	if($tmp_post->post_excerpt) {
		$excerpt = $tmp_post->post_excerpt;
	
	// If the user added a more tag, use that content.
	} else if(stristr($tmp_post->post_content, '<!--more-->') !== false) {
		$excerpt = explode('<!--more-->', $tmp_post->post_content);
		$excerpt = array_shift($excerpt);
		$excerpt = strip_tags(apply_filters('the_content', $excerpt));
	
	// If all else fails, generate an excerpt based on the max words.
	} else {
		$content = $tmp_post->post_content;
		$content = trim(preg_replace('/\s+/', ' ', strip_tags(apply_filters('the_content', $content))));
		$excerpt = explode(' ', $content);
		$excerpt = array_slice($excerpt, 0, $max_words);
		$excerpt = implode(' ', $excerpt);
		if(preg_match('/[A-Za-z0-9]/', substr($excerpt, -1))) {
			$excerpt .= '...';
		}
	}
	
	// Apply filters to allow the developer to change it.
	$excerpt = apply_filters('launchpad_excerpt', $excerpt);
	
	if($echo) {
		echo $excerpt;
	}
	return trim($excerpt);
}


/**
 * Get SEO'd Excerpt if Available
 *
 * @param		$max_words int The number of words to use if we generate from content based on space characters
 * @param		$echo bool Whether or not to echo the excerpt
 * @param		$id int The Post to use.  Default is the current post.
 * @since		1.0
 */
function launchpad_seo_excerpt($max_words = 32, $echo = false, $id = false) {
	global $post;
	
	// If an ID was passed, get the individual post.
	if($id !== false && (int) $id) {
		$tmp_post = get_post($id);
	}
	
	// If there was no post based on the ID, use the global $post.
	if(!$tmp_post) {
		$tmp_post = $post;
	}
	
	// Get the SEO information for the post.
	$seo = get_post_meta($tmp_post->ID, 'SEO', true);
	
	// If a meta description was set, use it.
	if(isset($seo['meta_description']) && trim($seo['meta_description'])) {
		if($echo) {
			echo $seo['meta_description'];
		}
		return trim($seo['meta_description']);
	
	// If not, get the auto-generated excerpt.
	} else {
		return launchpad_excerpt($max_words, $echo, $id);
	}
}


/**
 * Modify body classes to something more useful
 * 
 * This is modified from the Roots theme.
 *
 * @param		array $classes The WordPress-created classes for the body
 * @since		1.0
 */
function launchpad_modify_body_class($classes) {
	
	// If we're on a single page/post, add a class that matches the slug.
	if (is_single() || is_page() && !is_front_page()) {
		$classes[] = basename(get_permalink());
	}
	
	// Implode the classes array.
	$classes = implode(' ', $classes);
	
	// Replace some of the classes we don't need.
	$classes = preg_replace(
			array(
				'/page-template-.+? /',
				'/page-id-\d+ /',
				'/parent-pageid-\d+ /',
			),
			'', 
			$classes
		);
	
	// Explode the string back to an array.
	$classes = explode(' ', $classes);
	
	// Apply filters to allow the developer to change it.
	$classes = apply_filters('launchpad_body_class', $classes);

	return $classes;
}
add_filter('body_class', 'launchpad_modify_body_class');


/**
 * Remove WordPress's header junk
 *
 * Removes useless header junk so the developer can DIY.
 * This is modified from the Roots theme.
 *
 * @since		1.0
 */
function launchpad_head_cleanup() {
	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
	add_filter('use_default_gallery_style', '__return_null');
}

add_action('init', 'launchpad_head_cleanup');


/** 
 * Set a couple of definitions for the template.
 *
 * @since		1.0
 */
function launchpad_set_page_defines() {
	global $site_options;
	
	/** Google Analytics ID. */
	define('GA_ID', $site_options['google_analytics_id']);

	/** Whether to use cache. If things get weird, turn it off by setting the value to 0. */
	define('USE_CACHE', (int) $site_options['cache_timeout']);
}
add_action('template_redirect', 'launchpad_set_page_defines');


/**
 * A More Semantic Gallery Shortcode Handler
 *
 * @param		array $attr The attributes from the shortcode.
 * @since		1.0
 */
function launchpad_gallery_shortcode($attr) {
	// Defaults.
	$linkto = 'post';  // Also file and none.
	$columns = 3;
	
	// Trim the attributes.
	foreach($attr as $k => $v) {
		$attr[$k] = trim($v);
	}
	
	// Return if no ids are present.
	if($attr['ids'] === '') {
		return '';
	}
	
	// Normalize the columns.
	if($attr['columns']) {
		$columns = (int) $attr['columns'];
		if($columns < 1) {
			$columns = 3;
		}
	}
	
	// Determine the linkto, emulating WordPress's default behavior (I think).
	switch($attr['link']) {
		default:
			$linkto = 'post';
		break;
		case 'file':
			$linkto = 'file';
		break;
		case 'none':
			$linkto = 'none';
		break;
	}
	
	// Create the container element.
	$ret = '<figure class="gallery gallery-columns-' . $columns . '">';
	
	// Get the IDs as an array.
	$ids = explode(',', $attr['ids']);
	
	// Shuffle them if the order is random.
	if($attr['orderby'] === 'rand') {
		shuffle($ids);
	}
	
	// Loop the IDs.
	foreach($ids as $id) {
		// Get each gallery source.
		$thumb = wp_get_attachment_image_src($id, 'gallery');
		
		// If the link is to the post, generate the link.
		if($linkto === 'post') {
			$full_image = get_post($id);
			$ret .= '<a href="' . get_permalink($full_image->ID) . '">';
		
		// If the link is to a file, generate the file link.
		} else if($linkto === 'file') {
			$full_image = wp_get_attachment_image_src($id, 'full');
			$ret .= '<a href="' . $full_image[0] . '">';
		}
		
		// Generate the gallery image.
		$ret .= '<figure>';
		$ret .= '<img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt="">';
		$ret .= '</figure>';
		
		// Close the link if required.
		if($linkto !== 'none') {
			$ret .= '</a>';
		}
	}
	
	// Close the container.
	$ret .= '</figure>';
	
	return $ret;
}
remove_shortcode('gallery', 'gallery_shortcode');
add_shortcode('gallery', 'launchpad_gallery_shortcode');