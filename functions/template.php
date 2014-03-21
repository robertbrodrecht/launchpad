<?php

/**
 * Template Features
 *
 * Additional template tags and tweaks that affect the general front end of WordPress
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */


/**
 * Setup of basic theme support
 *
 * @since   	Version 1.0
 */
function launchpad_setup() {
	register_nav_menus(
			array(
				'primary' => 'Primary Navigation',
				'footer' => 'Footer Navigation',
			)
		);
	add_editor_style();
	//add_theme_support('post-formats', array('aside', 'gallery'));
	add_theme_support('post-thumbnails');
	add_editor_style('css/editor-style.css');
}
add_action('after_setup_theme', 'launchpad_setup');


/**
 * Setup of basic theme support
 *
 * @since   	Version 1.0
 * @link		https://developers.facebook.com/docs/opengraph/howtos/maximizing-distribution-media-content/
 */
function launchpad_image_setup() {
	global $image_sizes;
	add_image_size('opengraph', 1200, 1200, true);
	add_image_size('gallery', 300, 300, false);
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
 * @since   	Version 1.0
 */
function launchpad_title($echo = false) {
	global $post, $page, $paged;
	
	$vals = get_option('launchpad_site_options', '');
	
	$title = '';
	$title .= wp_title('', false);
	if ($paged >= 2 || $page >= 2) {
		$title .= ' (Page ' . max($paged, $page) . ')';
	}
	$title = ($title != '' ? $title . ' | ' : '');
	$title .= get_bloginfo('name') . ': ' . get_bloginfo('description', 'display');
	if($echo) {
		echo $title;
	}
	return $title;
}


/**
 * Create an excerpt
 *
 * @param		$max_words int The number of words to use if we generate from content based on space characters
 * @param		$echo bool Whether or not to echo the excerpt
 * @since   	Version 1.0
 */
function launchpad_excerpt($max_words = 60, $echo = false) {
	global $post;
	$max_words = (int) $max_words;
	if($post->post_excerpt) {
		$excerpt = $post->post_excerpt;
	} else if(stristr($post->post_content, '<!--more-->') !== false) {
		$excerpt = explode('<!--more-->', $post->post_content);
		$excerpt = array_shift($excerpt);
		$excerpt = strip_tags(apply_filters('the_content', $excerpt));
	} else {
		$content = $post->post_content;
		$content = trim(preg_replace('/\s+/', ' ', strip_tags(apply_filters('the_content', $content))));
		$excerpt = explode(' ', $content);
		$excerpt = array_slice($excerpt, 0, $max_words);
		$excerpt = implode(' ', $excerpt);
		if(preg_match('/[A-Za-z0-9]/', substr($excerpt, -1))) {
			$excerpt .= '...';
		}
	}
	if($echo) {
		echo $excerpt;
	}
	return trim($excerpt);
}


/**
 * Modify body classes to something more useful
 * This is modified from the Roots theme.
 *
 * @param		array $classes The WordPress-created classes for the body
 * @since   	Version 1.0
 */
function launchpad_modify_body_class($classes) {
	if (is_single() || is_page() && !is_front_page()) {
		$classes[] = basename(get_permalink());
	}

	$home_id_class = 'page-id-' . get_option('page_on_front');
	$remove_classes = array(
			'page-template-default',
			$home_id_class
		);
	$classes = array_diff($classes, $remove_classes);

	return $classes;
}
add_filter('body_class', 'launchpad_modify_body_class');


/**
 * Remove WordPress's header junk
 *
 * Removes useless header junk so the developer can DIY.
 * This is modified from the Roots theme.
 *
 * @since   	Version 1.0
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
 * A More Semantic Gallery Shortcode Handler
 *
 * @param		array $attr The attributes from the shortcode.
 * @since   	Version 1.0
 */
function launchpad_gallery_shortcode($attr) {
	// Defaults.
	$linkto = 'post';  // Also file and none.
	$columns = 3;
	
	foreach($attr as $k => $v) {
		$attr[$k] = trim($v);
	}
	
	if($attr['ids'] === '') {
		return '';
	}
	
	if($attr['columns']) {
		$columns = (int) $attr['columns'];
		if($columns < 1) {
			$columns = 3;
		}
	}
	
	switch($attr['columns']) {
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
	
	
	$ret = '<figure class="gallery gallery-columns-' . $columns . '">';
	$ids = explode(',', $attr['ids']);
	
	if($attr['orderby'] === 'rand') {
		shuffle($ids);
	}
	
	$imgs = array();
	foreach($ids as $id) {
		$thumb = wp_get_attachment_image_src($id, 'gallery');
		
		if($linkto === 'post') {
			$full_image = get_post($id);
			$ret .= '<a href="' . get_permalink($full_image->ID) . '">';
		} else if($linkto === 'file') {
			$full_image = wp_get_attachment_image_src($id, 'full');
			$ret .= '<a href="' . $full_image[0] . '">';
		}
		
		$ret .= '<figure>';
		$ret .= '<img src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt="">';
		$ret .= '</figure>';
		
		if($linkto !== 'none') {
			$ret .= '</a>';
		}
	}
	$ret .= '</figure>';
	
	return $ret;
}
remove_shortcode('gallery', 'gallery_shortcode');
add_shortcode('gallery', 'launchpad_gallery_shortcode');