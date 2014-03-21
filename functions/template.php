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
 * Get Unique Site String
 *
 * @since   	Version 1.0
 */
function launchpad_site_unique_string() {
	return md5($_SERVER['HTTP_HOST']);
}


/**
 * Get Current Cache ID
 *
 * @since   	Version 1.0
 */
function launchpad_get_cache_id() {
	global $post, $wp_query;
	
	$cache_id = false;
	
	if($wp_query->is_singular) {
		$cache_id = $post->ID;
	} else if($wp_query->is_archive) {
		$cur_tax = $wp_query->get_queried_object();
		$cache_id = 'archive-' . $cur_tax->taxonomy . '-' . $cur_tax->slug;
	} else if($wp_query->is_posts_page) {
		$cache_id = $post->post_type . '-' . md5($_SERVER['REQUEST_URI']);
	} else {
		$cache_id = md5($_SERVER['REQUEST_URI']);
	}
	
	if(!empty($_GET)) {
		$cache_id .= '-GET+' . md5(json_encode($_GET));
	}
	if(!empty($_POST)) {
		$cache_id .= '-POST+' . md5(json_encode($_POST));
	}
	
	return $cache_id;
}

/**
 * Get the Cache File for A Post
 *
 * @since   	Version 1.0
 */
function launchpad_get_cache_file($post_id = false, $type = false) {
	if(!USE_CACHE) {
		return false;
	}
	
	$site_unique_string = launchpad_site_unique_string();
	
	if($post_id && !$type) {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string . '/launchpad_post_cache-' . $post_id . '-file.html';		
	} else if($post_id && $type) {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string  . '/launchpad_post_cache-' . $post_id . '-' . $type . '-file.html';
	} else {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string  . '/';
	}
	
	return $cache;
}

/**
 * Check If A Cache Is Valid
 *
 * @since   	Version 1.0
 */
function launchpad_cached($post_id, $type) {
	global $site_options;
	
	if(!USE_CACHE || is_user_logged_in()) {
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- Cache disabled because ' . (!USE_CACHE ? 'caching is disabled.' : 'you are logged in.') . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	$cache = launchpad_get_cache_file($post_id, $type);
	if(file_exists($cache) && time()-filemtime($cache) < USE_CACHE) {
		readfile($cache);
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- USED ' . (time()-filemtime($cache)) . ' SECOND OLD CACHE @ ' . $cache . ' -->';
			echo "\n\n";
		}
		return true;
	} else {
		ob_start();
		return false;
	}
}


/**
 * Cache a Post Part
 *
 * @since   	Version 1.0
 */
function launchpad_cache($post_id, $type) {
	global $site_options;
	
	if(!USE_CACHE || is_user_logged_in()) {
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- Not generating cache because ' . (!USE_CACHE ? 'caching is disabled.' : 'you are logged in.') . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	$cache_content = ob_get_contents();
	
	$cache_folder = launchpad_get_cache_file();
	if(!file_exists($cache_folder)) {
		mkdir($cache_folder, 0777, true);
	}
	
	$cache = launchpad_get_cache_file($post_id, $type);
	$f = fopen($cache, 'w');
	fwrite($f, $cache_content);
	fclose($f);
	if($site_options['cache_debug_comments']) {
		echo "\n";
		echo '<!-- CREATED CACHE @ ' . $cache . ' -->';
		echo "\n\n";
	}
}


/**
 * Clear Post Part Cache
 *
 * @since   	Version 1.0
 */
function launchpad_clear_cache($post_id) {
	global $post;
	
	if(!$post_id) {
		return $post_id;
	}

	$cachefolder = launchpad_get_cache_file();
	if(file_exists($cachefolder)) {
		if($handle = opendir($cachefolder)) {
			while(false !== ($entry = readdir($handle))) {
				$entry_parts = explode('-', $entry);
				if(
					$entry_parts[0] === 'launchpad_post_cache' && 
					(
						(int) $entry_parts[1] === (int) $post_id || 
						$entry_parts[1] === $post->post_type || 
						$entry_parts[1] === 'archive'
					)
				) {
					unlink($cachefolder . $entry);
				}
			}
		}
	}
	return $post_id;
}
add_action('save_post', 'launchpad_clear_cache');


/**
 * Clear All Caches
 *
 * @since   	Version 1.0
 */
function launchpad_clear_all_cache() {
	$cachefolder = launchpad_get_cache_file();
	if(file_exists($cachefolder)) {
		if($handle = opendir($cachefolder)) {
			while(false !== ($entry = readdir($handle))) {
				$entry_parts = explode('-', $entry);
				if($entry_parts[0] === 'launchpad_post_cache') {
					unlink($cachefolder . $entry);
				}
			}
		}
	}
	return $post_id;
}
add_action('wp_update_nav_menu', 'launchpad_clear_all_cache');


/**
 * Get Template Part Passthru with Caching
 *
 * @since   	Version 1.0
 */
function launchpad_get_template_part($slug = false, $name = false) {
	$cache_id = launchpad_get_cache_id();
	
	if(!$slug) {
		return false;
	}
	
	if(is_admin()) {
		if($name) {
			get_template_part($slug, $name);
		} else {
			get_template_part($slug);
		}
		return;
	}
	
	if($name) {
		if(!launchpad_cached($cache_id, $slug . '+' . $name)) {
			get_template_part($slug, $name);
			launchpad_cache($cache_id, $slug . '+' . $name);
		}
	} else {
		if(!launchpad_cached($cache_id, $slug)) {
			get_template_part($slug);
			launchpad_cache($cache_id, $slug);
		}
	}
}


/**
 * Get Nav Menu Passthru with Caching
 *
 * @since   	Version 1.0
 */
function launchpad_wp_nav_menu($args) {
	$cache_id = launchpad_get_cache_id();
	$nav_id = md5(json_encode($args));
	
	if(!launchpad_cached($cache_id, 'navigation-' . $nav_id)) {
		wp_nav_menu($args);
		launchpad_cache($cache_id, 'navigation-' . $nav_id);
	}
}


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