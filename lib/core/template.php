<?php

/**
 * Template Features
 *
 * Additional template tags and tweaks that affect the general front end of WordPress
 *
 * @package 	Launchpad
 * @since		1.0
 */


if(class_exists('GFForms')) {
	// As a precaution, trigger a filter to put Gravity Forms code in footer below the jQuery include.
	add_filter('gform_init_scripts_footer', '__return_true');
	// Gravity forms tabindex is often harmful.  Turn it off.
	add_filter('gform_tabindex', create_function('', 'return false;'));
}


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
	
	launchpad_image_setup();
}
add_action('after_setup_theme', 'launchpad_theme_setup');


/**
 * Enqueue Scripts
 *
 * @since		1.3
 */
function launchpad_enqueue_scripts() {
	$load_footer = get_option('active_plugins') ? false : true;
	if(!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', get_template_directory_uri() . '/js/jquery-1.11.1.min.js', false, null, $load_footer);
		wp_enqueue_script('jquery');
		wp_register_script('launchpad_main', get_template_directory_uri() . '/js/main-min.js', array('jquery'), null, $load_footer);
		wp_enqueue_script('launchpad_main');
	}
}
add_action('wp_enqueue_scripts', 'launchpad_enqueue_scripts', 100);


/**
 * Defer Script Loading
 *
 * @since		1.3
 */
function launchpad_defer_scripts($url) {
	if(strpos($url, '.js') === false) {
		return $url;
	}
	return "$url' defer='defer";
}
if(!is_admin()) {
	add_filter('clean_url', 'launchpad_defer_scripts', 11, 1);
}


/**
 * Setup of basic theme support
 *
 * @since		1.0
 * @link		https://developers.facebook.com/docs/opengraph/howtos/maximizing-distribution-media-content/
 * @see			launchpad_theme_setup()
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
	
	// Append the site name and description to the title.
	$description = get_bloginfo('description', 'display');
	$title .= get_bloginfo('name') . ($description ? ': ' . $description : '');
	
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
	
	$tmp_post = false;
	
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
	
	$tmp_post = false;
	
	// If an ID was passed, get the individual post.
	if($id !== false && (int) $id) {
		$tmp_post = get_post($id);
	}
	
	// If there was no post based on the ID, use the global $post.
	if(!$tmp_post) {
		$tmp_post = $post;
	}
	
	if(!$tmp_post) {
		return;
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
 * Modify body classes to something more useful
 * 
 * This is modified from the Roots theme.
 *
 * @param		array $classes The WordPress-created classes for the nav item
 * @param		object $item The current menu item.
 * @since		1.3
 */
function launchpad_modify_nav_class($classes, $item) {
	$slug = sanitize_title($item->title);
	$classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);
	
	$classes[] = 'menu-' . $slug;
	
	$link_url = preg_replace('|^https?://' . $_SERVER['HTTP_HOST'] . '/|', '/', $item->url);
	$current_url = $_SERVER['REQUEST_URI'];
	
	if($link_url != '/' && $current_url !== $link_url && stristr($current_url, $link_url) !== false) {
		$classes[] = 'current-hierarchy-ancestor';
	}
	
	$classes = array_unique($classes);
	
	return array_filter(
		$classes, 
		function($el) {
			$el = trim($el);
			return empty($el) ? false : true;
		}
	);
}
//add_filter('nav_menu_css_class', 'launchpad_modify_nav_class', 10, 2);


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
	remove_action('wp_head', 'noindex', 1);
	remove_action('wp_head', 'rel_canonical', 10);
	
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
	define('GA_ID', isset($site_options['google_analytics_id']) ?  $site_options['google_analytics_id'] : '');

	/** Whether to use cache. If things get weird, turn it off by setting the value to 0. */
	define('USE_CACHE', isset($site_options['cache_timeout']) ? (int) $site_options['cache_timeout'] : 0);
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


/**
 * Modify Search Join Query To Include Flexible Content
 *
 * @param		string $q The query.
 * @since		1.0
 */
function launchpad_search_flexible_join($q) {
	global $wpdb;
	
	// We only want to do this on a search page.
	if(is_search()) {
		// Get the post types so we can find out what flexible types there are.
		$types = launchpad_get_post_types();
		
		// This keeps up with what flexible types we have already considered.
		$used_flex = array();
		$used_meta = array();
		
		// If we have post types.
		if($types) {
			
			// Loop the post types.
			foreach($types as $type) {
				
				// If the type has flexible content.
				if($type['flexible']) {
					
					// Loop the flexible content.
					foreach($type['flexible'] as $flex_name => $flex_value) {
						
						// If we haven't used the flexible content, add it to the query.
						if(!in_array($flex_name, $used_flex)) {
							$used_flex[] = $flex_name;
							$q .= " LEFT JOIN $wpdb->postmeta launchpad_search_$flex_name ON $wpdb->posts.ID = launchpad_search_$flex_name.post_id AND launchpad_search_$flex_name.meta_key = '$flex_name' ";
						}
					}
				}
				
				// If the type has metabox content.
				if($type['metaboxes']) {
					
					// Loop the metabox content.
					foreach($type['metaboxes'] as $meta_name => $meta_value) {
						
						// If we haven't used the metabox content, add it to the query.
						if(!in_array($meta_name, $used_meta)) {
							$used_meta[] = $meta_name;
							$q .= " LEFT JOIN $wpdb->postmeta launchpad_search_$meta_name ON $wpdb->posts.ID = launchpad_search_$meta_name.post_id AND launchpad_search_$meta_name.meta_key = '$meta_name' ";
						}
					}
				}
			}
		}
	}
	return $q;
}
add_action('posts_join', 'launchpad_search_flexible_join');


/**
 * Modify Search Where Query To Include Flexible Content
 *
 * @param		string $q The query.
 * @since		1.0
 */
function launchpad_search_flexible_where($q) {
	global $wpdb;
	
	// We only want to do this on a search page.
	if(is_search()) {
		// Get the post types so we can find out what flexible types there are.
		$types = launchpad_get_post_types();
		
		// This keeps up with what flexible types we have already considered.
		$used_flex = array();
		$used_meta = array();
		
		// If we have post types.
		if($types) {
		
			// Loop the post types.
			foreach($types as $type) {
				
				// If the type has flexible content.
				if($type['flexible']) {
					
					// Loop the flexible content.
					foreach($type['flexible'] as $flex_name => $flex_value) {
					
						// If we haven't used the flexible content, add it to the query.
						// Unfortunately, the WHERE is already pretty populated by now.
						// We can't just shove this onto the end. So, we use the post_title
						// part of the WHERE as a template and use preg_replace trickery
						// to put the right stuff in the right place.
						if(!in_array($flex_name, $used_flex)) {
							$used_flex[] = $flex_name;
							$q = preg_replace("/(\($wpdb->posts.post_title LIKE '(%.*?%)'\))/", '$1 OR (launchpad_search_' . $flex_name . '.meta_value LIKE "$2")', $q);
						}
					}
				}
				
				// If the type has metabox content.
				if($type['metaboxes']) {
					
					// Loop the metabox content.
					foreach($type['metaboxes'] as $meta_name => $meta_value) {
					
						// If we haven't used the metabox content, add it to the query.
						// Unfortunately, the WHERE is already pretty populated by now.
						// We can't just shove this onto the end. So, we use the post_title
						// part of the WHERE as a template and use preg_replace trickery
						// to put the right stuff in the right place.
						if(!in_array($meta_name, $used_meta)) {
							$used_meta[] = $meta_name;
							$q = preg_replace("/(\($wpdb->posts.post_title LIKE '(%.*?%)'\))/", '$1 OR (launchpad_search_' . $meta_name . '.meta_value LIKE "$2")', $q);
						}
					}
				}
			}
		}
	}
	return $q;
}
add_action('posts_search', 'launchpad_search_flexible_where');


/**
 * Attempt to find a flexible content module either in the custom folder or core folder.
 *
 * @param		string $type The type of flexible content to find.
 * @since		1.0
 */
function launchpad_find_flexible_content($type = '') {
	if($type === '') {
		return '';
	}
	$attempt = locate_template('flexible/custom/' . $type);
	if($attempt) {
		return $attempt;
	} else {
		return locate_template('flexible/core/' . $type);
	}
}