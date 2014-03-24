<?php
/**
 * Content Cache Functions
 *
 * @since		1.0
 */


/**
 * Get Unique Site String
 *
 * @since		1.0
 */
function launchpad_site_unique_string() {
	return md5($_SERVER['HTTP_HOST']);
}


/**
 * Get Current Cache ID
 *
 * @since		1.0
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
 * @since		1.0
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
	
	$cache = apply_filters('launchpad_cache_file_path', $cache, $post_id, $type);
	
	return $cache;
}

/**
 * Check If A Cache Is Valid
 *
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
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
 * @since		1.0
 */
function launchpad_wp_nav_menu($args) {
	$cache_id = launchpad_get_cache_id();
	$nav_id = md5(json_encode($args));
	
	if(!launchpad_cached($cache_id, 'navigation-' . $nav_id)) {
		wp_nav_menu($args);
		launchpad_cache($cache_id, 'navigation-' . $nav_id);
	}
}