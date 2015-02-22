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
 * Add Support for Custom CSS and JS for Critical Path
 *
 * @since		1.6
 */
function launchpad_add_head_modifications() {
	global $site_options, $wp_query;
	
	$add_this_id = $site_options['add_this_id'];
	
	$ajax = '';
	if(isset($site_options['ajax_page_loads']) && $site_options['ajax_page_loads'] === true) {
		$ajax = 'true';
	}
	
	$excerpt = launchpad_seo_excerpt();

	?>
	
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		
		<link rel="icon" href="/images/icons/favicon.png">
		<link rel="icon" href="/images/icons/favicon_2x.png" media="(-webkit-min-device-pixel-ratio: 2)">
		
		<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name') ?> RSS Feed" href="/feed/">
		<?php if(is_single() || is_page()) { ?>
		
		<link rel="canonical" href="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		<?php } ?>
		
		<link rel="apple-touch-icon" sizes="57x57"   href="/images/icons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="72x72"   href="/images/icons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/images/icons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/images/icons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/images/icons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/images/icons/apple-touch-icon-152x152.png">
		
		<link href="/images/icons/startup-iphone-320x460.jpg" rel="apple-touch-startup-image" media="(device-width: 320px)">
		<link href="/images/icons/startup-iphone4-640x920.jpg" rel="apple-touch-startup-image" media="(device-width: 320px) and (-webkit-min-device-pixel-ratio: 2)">
		<link href="/images/icons/startup-iphone5-640x1096.jpg" rel="apple-touch-startup-image" media="(device-width: 320px) and (device-height: 568px) and (-webkit-min-device-pixel-ratio: 2)">
		<link href="/images/icons/ipad-portrait-768x1004.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait)">
		<link href="/images/icons/ipad-landscape-1024x748.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape)">
		<link href="/images/icons/ipad-retina-portrait-1536x2008.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)">
		<link href="/images/icons/ipad-retina-landscape-2048x1496.jpg" rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)">
		
		<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name') ?>">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		
		<meta name="description" content="<?php echo $excerpt; ?>">
		<?php
		
		if(!get_option('blog_public')) {
			echo '<meta name="robots" content="noindex, nofollow">';			
		} else if(!$wp_query->is_single && !$wp_query->is_singular && !is_front_page()) {
			echo '<meta name="robots" content="noindex, follow">';
		}
		
		?>
		<?php if(isset($site_options['fb_app_id']) && $site_options['fb_app_id']) { ?>
		
		<meta property="fb:app_id" content="<?php echo $site_options['fb_app_id'] ?>">
		<?php } ?>
		<?php if(isset($site_options['fb_admin_id']) && $site_options['fb_admin_id']) { ?>
		<?php foreach(explode(',', $site_options['fb_admin_id']) as $fb_admin_id) { ?>
		
		<meta property="fb:admins" content="<?php echo trim($fb_admin_id) ?>">
		<?php } ?>
		<?php } ?>
		
		<?php
		
		$card_type = 'website';
		if(is_single() || is_singular()) {
			$card_type = 'article';
		}
		
		?>
		
		<meta property="og:title" content="<?php launchpad_title(true); ?>">
		<meta property="og:description" content="<?php echo $excerpt; ?>">
		<meta property="og:type" content="<?php echo $card_type ?>">
		<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		<meta property="og:site_name" content="<?php bloginfo('name') ?>">
		<?php
		
		if(has_post_thumbnail()) {
			$thumbnail = get_post_thumbnail_id();
			$thumbnail = wp_get_attachment_image_src($thumbnail, 'opengraph');
			if($thumbnail) {
				?>
				
		<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php echo $thumbnail[0] ?>">
		<meta property="og:image:width" content="<?php echo $thumbnail[1] ?>">
		<meta property="og:image:height" content="<?php echo $thumbnail[2] ?>">
				<?php
			}
		}
		
		?>
	
		<?php
		
		$card_type = 'summary';
		if((is_single() || is_singular()) && has_post_thumbnail()) {
			$card_type = 'summary_with_large_image';
		}
		
		?>
	
		<meta property="twitter:card" content="<?php echo $card_type ?>">
		<meta property="twitter:url" content="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php the_permalink(); ?>">
		<meta property="twitter:title" content="<?php launchpad_title(true); ?>">
		<meta property="twitter:description" content="<?php echo $excerpt; ?>">
		<?php
		
		if(has_post_thumbnail()) {
			$thumbnail = get_post_thumbnail_id();
			$thumbnail = wp_get_attachment_image_src($thumbnail, 'large'); // Large to hopefully stay under 1MB.
			if($thumbnail) {
				?>
	
		<meta property="twitter:image" content="http://<?php echo $_SERVER['HTTP_HOST'] ?><?php echo $thumbnail[0] ?>">
		<meta property="twitter:image:width" content="<?php echo $thumbnail[1] ?>">
		<meta property="twitter:image:height" content="<?php echo $thumbnail[2] ?>">
				<?php
			}
		}
		
		?>
		<?php if(isset($site_options['twitter_card_username']) && $site_options['twitter_card_username']) { ?>
		
		<meta property="twitter:site" content="@<?php echo $site_options['twitter_card_username'] ?>">
		<?php } ?>
	
		<?php if(defined('GA_ID') && GA_ID != '') { ?>
	
		<script id="google-analytics">
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', '<?php echo GA_ID ?>', 'auto');
		  ga('send', 'pageview');
		</script>
		<?php 
		
		}
	
		if(stristr($_SERVER['HTTP_HOST'], '.dev') !== false || stristr($_SERVER['HTTP_HOST'], '.git') !== false) {
			echo "<script>window.dev = true;</script>\n";
		}
}
add_action('wp_head', 'launchpad_add_head_modifications', 1);


/**
 * Enqueue Scripts
 *
 * @since		1.3
 */
function launchpad_enqueue_scripts() {
	global $site_options;
	
	$load_footer = get_option('active_plugins') ? false : true;
	if(!is_admin()) {
		
		wp_enqueue_style('launchpad_screen', '/css/screen.css', array(), null, 'screen, projection, handheld, tv');
		wp_enqueue_style('launchpad_print', '/css/print.css', array(), null, 'print');
		
		wp_deregister_script('jquery');
		wp_register_script('jquery', get_template_directory_uri() . '/js/jquery-1.11.1.min.js', false, null, $load_footer);
		wp_enqueue_script('jquery');
		wp_register_script('launchpad_main', get_template_directory_uri() . '/js/main-min.js', array('jquery'), null, $load_footer);
		wp_enqueue_script('launchpad_main');
		
		if($site_options['ajax_page_loads']) {
			wp_register_script('launchpad_ajax', get_template_directory_uri() . '/js/launchpad-ajax.js', array('jquery', 'launchpad_main'), null, $load_footer);
			wp_enqueue_script('launchpad_ajax');
		}
	}
}
add_action('wp_enqueue_scripts', 'launchpad_enqueue_scripts', 100);


/**
 * Defer Script Loading
 *
 * @since		1.3
 */
function launchpad_defer_scripts($url) {
	global $site_options;
	if(is_admin() || (isset($site_options['defer_js']) && !$site_options['defer_js']) || strpos($url, '.js') === false) {
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
	add_image_size('gallery', 300, 300, true);
	
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
	} else if($current_url === $link_url) {
		$classes[] = 'current-hierarchy-page';		
	}
	
	$classes = array_unique($classes);
	
	// Apply filters to allow the developer to change it.
	$classes = apply_filters('launchpad_nav_class', $classes);
	
	return array_filter(
		$classes, 
		function($el) {
			$el = trim($el);
			return empty($el) ? false : true;
		}
	);
}
add_filter('nav_menu_css_class', 'launchpad_modify_nav_class', 10, 2);


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
 * Register Widget Area
 *
 * @since		1.4
 */
function launchpad_register_widget() {
	register_sidebar(
		array(
			'name' => 'Blog Sidebar',
			'id' => 'blog_sidebar',
			'before_widget' => '<section id="flexible-widget">',
			'after_widget' => '</section>',
			'before_title' => '<h1>',
			'after_title' => '</h1>',
		)
	);
	
	$query = new WP_Query(
		array(
			'posts_per_page' => -1,
			'post_type' => 'any',
			'post_status' => 'any',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => '_wp_page_template',
					'compare' => 'LIKE',
					'value' => 'sidebar'
				)
			),
			'fields' => 'ids'
		)
	);
	
	foreach($query->posts as $post) {
		$post = get_post($post);
		if($post->post_parent === 0 || !$post->sidebar_flexible_inherit_from_parent) {
			register_sidebar(
				array(
					'name' => $post->post_title . ' Sidebar',
					'id' => 'page_' . $post->ID . '_sidebar',
					'before_widget' => '<section id="flexible-widget">',
					'after_widget' => '</section>',
					'before_title' => '<h1>',
					'after_title' => '</h1>',
				)
			);
		}
	}
}
add_action('widgets_init', 'launchpad_register_widget');


/**
 * Wrap YouTube video in the video-contianer wrapper.
 *
 * @param		str $html The embed HTML
 * @param		str $url The video URL
 * @since		1.4
 */
function add_video_embed_note($html, $url) {
	if(preg_match('|https?://w*?\.?youtu|', $url) || preg_match('|https?://w*?\.?vimeo\.com|', $url)) {
		$html = '<div class="video-container">' . $html . '</div>';
	}
	return $html;
}
add_filter('embed_oembed_html', 'add_video_embed_note', 10, 3);


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
 * A More Semantic Caption Shortcode Handler
 *
 * @param		array $attr The attributes from the shortcode.
 * @param		string $content The content of the shortcode.
 * @since		1.0
 */
function launchpad_caption_shortcode($attr, $content) {
	preg_match_all('/<img.*?src="(.*?)".*?>/', $content, $imgs);
	preg_match_all('/<a.*?href="(.*?)".*?>/', $content, $links);
	
	$content = preg_replace('/^.*?</', '<', $content);
	if(preg_match('/^<a/', $content)) {
		$content = preg_replace('/^<a.*?>.*?<\/a>/', '', $content);
	} else {
		$content = preg_replace('/^<img.*?>/', '', $content);
	}
	
	$return = '<figure class="wp-caption';
	if($attr['align']) {
		$return .= ' ' . $attr['align'];
	}
	$return .= '"';
	if($attr['width']) {
		$return .= ' style="width: ' . $attr['width'] . 'px;"';
	}
	$return .= '>';
	if(isset($links[0][0])) {
		$return .= $links[0][0];
	}
	if(isset($imgs[0][0])) {
		$return .= $imgs[0][0];
	}
	if(isset($links[0][0])) {
		$return .= '</a>';
	}
	$return .= '<figcaption>' . $content . '</figcaption>';
	$return .= '</figure>';
	
	return $return;
}
remove_shortcode('caption', 'img_caption_shortcode');
remove_shortcode('wp_caption', 'img_caption_shortcode');
add_shortcode('caption', 'launchpad_caption_shortcode');
add_shortcode('wp_caption', 'launchpad_caption_shortcode');


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
				if(isset($type['flexible'])) {
					
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
				if(isset($type['metaboxes'])) {
					
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
				if(isset($type['flexible'])) {
					
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
				if(isset($type['metaboxes'])) {
					
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


function launchpad_determine_best_template_file($post, $prefix = '') {
	// The post type is the default second parameter.
	$content_type = get_post_type();
	$content_format = get_post_format();
	
	$file_prefix = 'content-';
	if($prefix) {
		$file_prefix .= 'main-';
	}
	
	// If the content is singular, such as a page or single post type, handle accordingly.
	if(is_singular()) {
		// First, see if there is a content-main-slug.php
		if(locate_template($file_prefix . $post->post_name . '.php')) {
			$content_type = $post->post_name;
			
		// Next, if this is a page, see if there is a content-main-page-slug.php.
		} else if(is_page() && locate_template($file_prefix . '-page-' . $post->post_name . '.php')) {
			$content_type = 'page-' . $post->post_name;
			
		// Next, if this is a single post type with a content format, see if there is a content-main-posttype-format.php
		} else if(is_single() && $content_format && locate_template($file_prefix . $content_type . '-' . $content_format . '.php')) {
			$content_type = $content_type . '-' . $content_format;
			
		// Next, if this is a single post type with a content format, try content-main-format.php.
		} else if(is_single() && $content_format && locate_template($file_prefix . $content_format . '.php')) {
			$content_type = $content_format;
		
		// Next, if this is a single post type, try content-main-single-posttype.php.
		} else if(is_single() && locate_template($file_prefix . 'single-' . $content_type . '.php')) {
			$content_type = 'single-' . $content_type;
		
		// Finally, try content-main-single.php.
		} else if(is_single() && locate_template($file_prefix . 'single.php')) {
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
				
				// First, try content-main-tax-termslug.php.
				if(locate_template($file_prefix . $queried_taxonomy . '-' . $queried_term . '.php')) {
					$content_type = $queried_taxonomy . '-' . $queried_term;
				
				// Next, try content-main-tax.php.
				} else if(locate_template($file_prefix . $queried_taxonomy . '.php')) {
					$content_type = $queried_taxonomy;
					
				// Next, try content-main-archive-posttype.php
				} else if(locate_template($file_prefix . 'archive-' . $content_type . '.php')) {
					$content_type = 'archive-' . $content_type;
				
				// Finally, try content-main-archive.php.
				} else if(locate_template($file_prefix . 'archive.php')) {
					$content_type = 'archive';
				}
			
			// If it isn't a taxonomy, look for content-main-archive-posttype.php.
			} else if(locate_template($file_prefix . 'archive-' . $content_type . '.php')) {
				$content_type = 'archive-' . $content_type;
			
			// Finally, try content-main-archive.php.
			} else if(locate_template($file_prefix . 'archive.php')) {
				$content_type = 'archive';
			}
		
		// If there is no queried object, look for content-main-archive-posttype.php.
		} else if(locate_template($file_prefix . 'archive-' . $content_type . '.php')) {
			$content_type = 'archive-' . $content_type;
		
		// Finally, try content-main-archive.php.
		} else if(locate_template($file_prefix . 'archive.php')) {
			$content_type = 'archive';
		}
	
	
	// Finally, if we're on a search page, use search as the second parameter.
	} else if(is_search()) {
		$content_type = 'search';
	}
	
	$content_type = apply_filters('launchpad_determine_best_template_file', $content_type, $post, $prefix);
	
	return $content_type;
}

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


/**
 * Display Flexible Content
 *
 * @param		object $post The current post object to display flex content for.
 * @param		string $location The page location that references the flex's display.
 * @since		1.6
 */
function launchpad_flexible_content($post = false, $location = 'main') {
	if(!$post) {
		return;
	}
	
	// Handle the flexible content.
	// Get the post types.
	$post_types = launchpad_get_post_types();
	
	// If there is flexible content for our current post type, render the flexible content.
	if(isset($post_types) && isset($post_types[$post->post_type]['flexible'])) {
		
		// Loop the flexible types.
		foreach($post_types[$post->post_type]['flexible'] as $flexible_type => $flexible_details) {
			
			if(!isset($flexible_details['display'])) {
				$flexible_details['display'] = 'main';
			}
			
			// This is using the WordPress location as a signal for where the content will go.
			// I'm not entirely sure this is "good" or "smart," but I'm doing it anyway.
			if(trim($flexible_details['display']) === trim($location)) {
				
				// Get the post meta value for the current flexible type.
				$flexible = get_post_meta($post->ID, $flexible_type, true);
				
				// If there is any matching post meta, we need to render a field.
				if($flexible) {
					
					// Loop the values of the flexible content.
					foreach($flexible as $flex) {
						
						// Pull out key information from the flexible type.
						list($flex_type, $flex_values) = each($flex);
						$flexible_prototype = $flexible_details['modules'][$flex_type];
						
						// Use "include locate_template" so that variables are still in scope.
						switch($flex_type) {
							case 'accordion':
								include launchpad_find_flexible_content('accordion.php');
							break;
							case 'gallery':
								include launchpad_find_flexible_content('gallery.php');
							break;
							case 'link_list':
								include launchpad_find_flexible_content('link_list.php');
							break;
							case 'section_navigation':
								include launchpad_find_flexible_content('section_navigation.php');
							break;
							case 'simple_content':
								include launchpad_find_flexible_content('simple_content.php');
							break;
							default:
								$path = launchpad_find_flexible_content($flex_type . '.php');
								if($path) {
									include $path;
								} else {
									trigger_error('Could not find template for ' . $flex_type . ' flexible content.');
								}
							break;
						}
					}
				}
			}
		}
	}
}


/**
 * Create a sidebar
 *
 * @param		object $post The current post object to modify.
 * @since		1.6
 */
function launchpad_sidebar($post) {
	$sidebar_content = '';
	$posts_page = get_option('page_for_posts');
	
	if($post->post_type === 'post') {
		if(is_active_sidebar('blog_sidebar')) {
			dynamic_sidebar('blog_sidebar');
		} else {
			$posts_page = get_permalink($posts_page);
			
			$cats = get_categories();
			
			foreach($cats as $cat) {
				$sidebar_content .= '<li>';
				$sidebar_content .= '<a href="' . $posts_page . 'category/' . $cat->slug . '/">' . $cat->name . '</a>';
				$sidebar_content .= '</li>';
			}
			
			if($sidebar_content) {
				$sidebar_content = '<nav><h1>Categories</h1><ul>' . $sidebar_content . '</ul></nav>';
			}
		}
	}
	
	ob_start();
	launchpad_flexible_content($post, 'sidebar');
	$sidebar_flexible = ob_get_contents();
	ob_end_clean();
	
	$widget_location = 'below';
	$widget_id = $post->ID;
	
	
	if($sidebar_flexible) {
		if($post->sidebar_widget_location == 'above') {
			$widget_location = 'above';
		}
		$sidebar_content .= $sidebar_flexible;
	} else if($post->sidebar_flexible_inherit_from_parent && $post->post_parent > 0) {
		$tmp_post = $post;
		while(!$sidebar_flexible && $tmp_post->post_parent) {
			$tmp_post = get_post($tmp_post->post_parent);
			ob_start();
			launchpad_flexible_content($tmp_post, 'sidebar');
			$sidebar_flexible = ob_get_contents();
			ob_end_clean();
			if($sidebar_flexible) {
				break;
			}
		}
		if($sidebar_flexible) {
			if($tmp_post->sidebar_widget_location == 'above') {
				$widget_location = 'above';
			}
			$widget_id = $tmp_post->ID;
			$sidebar_content .= $sidebar_flexible;
		}
	}
	
	$sidebar_content = apply_filters('launchpad_sidebar_content', $sidebar_content, $post);
	
	
	if($widget_location === 'above') {
		dynamic_sidebar('page_' . $widget_id . '_sidebar');
	}
	echo $sidebar_content;
	if($widget_location !== 'above') {
		dynamic_sidebar('page_' . $widget_id . '_sidebar');
	}
}
add_action('launchpad_sidebar', 'launchpad_sidebar', 1);


/**
 * A Function to Filter to Force Left Sidebar
 *
 * @since		1.6
 */
function launchpad_force_top_sidebar() {
	return 'above';
}


/**
 * A Function to Filter to Force Right Sidebar
 *
 * @since		1.6
 */
function launchpad_force_bottom_sidebar() {
	return 'below';
}