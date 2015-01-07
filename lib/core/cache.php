<?php

/**
 * Content Cache Functions
 *
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Get Unique Site String
 * 
 * The site unique string is simply a hash of the host.
 *
 * @since		1.0
 */
function launchpad_site_unique_string() {
	return md5($_SERVER['HTTP_HOST'] . CHILD_THEME_PATH);
}


/**
 * Add a field to media that will allow for media replacement.
 * 
 * @since		1.3
 */
 function launchpad_temp_dir() {
	$system_temp = sys_get_temp_dir();
	
	if(!file_exists($system_temp) || !is_writable($system_temp)) {
		if(is_writable($_SERVER['DOCUMENT_ROOT'])) {
			$system_temp = $_SERVER['DOCUMENT_ROOT'] . '/tmp/';
			$system_temp = str_replace('//', '/', $system_temp);
			@mkdir($system_temp, 0777, true);
		}
		
		if(file_exists($system_temp) && is_writable($system_temp)) {
			return $system_temp;
		} else {
			return false;
		}
	}
	
	return $system_temp;
 }


/**
 * Get Current Cache ID
 *
 * @since		1.0
 */
function launchpad_get_cache_id() {
	global $post, $wp_query;
	
	if(!$post) {
		return md5($_SERVER['REQUEST_URI']);
	}
	
	// If the post is singular, the cache id is the post id.
	if($wp_query->is_singular || $wp_query->is_single) {
		$cache_id = $post->ID;
	
	// If we're on an archive page, get the the taxonomy and slug as the cache id.
	} else if($wp_query->is_archive) {
		$cur_tax = $wp_query->get_queried_object();
		if($cur_tax && isset($cur_tax->taxonomy)) {
			$cache_id = 'archive-' . $cur_tax->taxonomy . '-' . $cur_tax->slug;
		} else {
			$cache_id = md5($_SERVER['REQUEST_URI']);
		}
	
	// If we're on a posts page, use the post type with the request URI as the cache id.
	} else if($wp_query->is_posts_page) {
		$cache_id = $post->post_type . '-' . md5($_SERVER['REQUEST_URI']);
	
	// Otherwise, fall back to using a hash of the request uri as the cache id.
	} else {
		$cache_id = md5($_SERVER['REQUEST_URI']);
	}
	
	// If there are GET or POST parameters, include those in the cache id.
	// This is to prevent collision for caches made to dynamic pages.
	if(!empty($_GET)) {
		$cache_id .= '-GET+' . md5(json_encode($_GET));
	}
	if(!empty($_POST)) {
		$cache_id .= '-POST+' . md5(json_encode($_POST));
	}
	
	// If there is a page parameter, append it.
	if(isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged']) {
		$cache_id .= '-PAGE+' . $wp_query->query_vars['paged'];
	}
	
	return $cache_id;
}


/**
 * Get the Cache File Path for A Post
 *
 * @param		int|bool $post_id The post we need the cache file path for.
 * @param		int|bool $type The post type of the post.
 * @uses		launchpad_site_unique_string()
 * @since		1.0
 */
function launchpad_get_cache_file($post_id = false, $type = false) {
	
	// If cache is disabled, return false.
	//if(!defined('USE_CACHE') || !USE_CACHE) {
	//	return false;
	//}
	
	// Get the site's unique string.
	$site_unique_string = launchpad_site_unique_string();
	
	$temp = launchpad_temp_dir();
	
	if($temp === false) {
		return false;
	}
	
	// Create the site cache folder if it doesn't exist.
	if(!file_exists($temp . '/' . $site_unique_string  . '/')) {
		@mkdir($temp . '/' . $site_unique_string  . '/', 0777, true);
	}
	
	// If the cache file is for a post but not a type, set a file name with just the ID.
	if($post_id && !$type) {
		$cache = $temp . '/' . $site_unique_string . '/launchpad_post_cache-' . $post_id . '-file.html';
	
	// If there is a post and type, set a file name with both ID and type.
	} else if($post_id && $type) {
		$cache = $temp . '/' . $site_unique_string  . '/launchpad_post_cache-' . $post_id . '-' . $type . '-file.html';
	
	// Otherwise, just return the folder.
	} else {
		$cache = $temp . '/' . $site_unique_string  . '/';
	}
	
	$cache = preg_replace('|//+|', '/', $cache);
	
	// Apply filters to allow the developer to modify the value.
	$cache = apply_filters('launchpad_cache_file_path', $cache, $post_id, $type);
	
	$cache_dir = dirname($cache);
	if(!file_exists($cache_dir)) {
		@mkdir($cache_dir, 077, true);
		if(!file_exists($cache_dir) || !is_writable($cache_dir)) {
			return false;
		}
	}
	
	return $cache;
}


/**
 * Check If A Cache Is Valid
 *
 * @param		int|bool $post_id The post we need to check the cache on.
 * @param		int|bool $type The post type of the post.
 * @see			launchpad_cache()
 * @since		1.0
 */
function launchpad_cached($post_id, $type) {
	global $site_options, $launchpad_is_caching;
	
	// If cache is disabled or the user is logged in, we don't cache.
	if(!USE_CACHE || is_user_logged_in() || $launchpad_is_caching) {
		if(isset($site_options['cache_debug_comments']) && $site_options['cache_debug_comments']) {
			// Default reason.
			$reason = 'caching is disabled.';
			
			// If the user is logged in, update the reason.
			if(is_user_logged_in()) {
				$reason = 'you are logged in.';
			}
			
			// If we're already within a cache, update the reason.
			if($launchpad_is_caching) {
				$reason = 'caching has already started for a page fragment that this cache request is included in: ' . $launchpad_is_caching;
			}
			
			echo "\n";
			echo '<!-- Cache disabled because ' . $reason . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	// Get the current cache file based on the post ID and type.
	$cache = launchpad_get_cache_file($post_id, $type);
	
	// If the cache file exists and hasn't expired, send the cached output to the browser.
	if($cache !== false && file_exists($cache) && time()-filemtime($cache) < USE_CACHE) {
		readfile($cache);
		if(isset($site_options['cache_debug_comments']) && $site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- USED ' . (time()-filemtime($cache)) . ' SECOND OLD CACHE @ ' . $cache . ' -->';
			echo "\n\n";
		}
		return true;
	
	// Otherwise, we need to turn on output buffering so we can cache the output.
	} else {
		$launchpad_is_caching = launchpad_get_cache_file($post_id, $type);
		ob_start();
		return false;
	}
}


/**
 * Cache a Post Part
 *
 * @param		int|bool $post_id The post we need the cache for.
 * @param		int|bool $type The post type of the post.
 * @see			launchpad_get_template_part()
 * @see			launchpad_cached()
 * @uses		launchpad_get_cache_file()
 * @since		1.0
 */
function launchpad_cache($post_id, $type) {
	global $site_options, $launchpad_is_caching;
	
	// Get the cache file for the current post and type.
	$cache = launchpad_get_cache_file($post_id, $type);
	
	// If cache is disabled or the user is logged in, we don't cache.
	if($cache === false || !USE_CACHE || is_user_logged_in() || $launchpad_is_caching != $cache) {
		if(isset($site_options['cache_debug_comments']) && $site_options['cache_debug_comments']) {
			// Default reason.
			$reason = 'caching is disabled.';
			
			// If the user is logged in, update the reason.
			if(is_user_logged_in()) {
				$reason = 'you are logged in.';
			}
			
			// If there is no cache folder because there is nowhere to write.
			if($cache === false) {
				$reason = 'the cache folder does not have write permissions.';
			}
			
			// If we're already within a cache, update the reason.
			if($launchpad_is_caching) {
				$reason = 'caching has already started for a page fragment that this cache request is included in: ' . $launchpad_is_caching;
			}
			
			echo "\n";
			echo '<!-- Cache disabled because ' . $reason . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	// If we get here, that means launchpad_cached() should have enabled output buffering.
	// Grab the output buffer's contents.
	$cache_content = ob_get_contents();
	
	// Get the site cache folder.  Since we pass no params, it will be the folder.
	$cache_folder = launchpad_get_cache_file();
	if($cache_folder !== false && !file_exists($cache_folder)) {
		@mkdir($cache_folder, 0777, true);
	}
	
	if(!$cache_folder || !is_writable($cache_folder)) {
		echo "\n";
		echo '<!-- COULD NOT WRITE CACHE @ ' . $cache . ' -->';
		echo "\n\n";
		$launchpad_is_caching = false;
		return;
	}
	
	// Save the output.  The live output will go to the screen.
	$f = fopen($cache, 'w');
	fwrite($f, $cache_content);
	fclose($f);
	
	// Leave a comment if configured to.
	if($site_options['cache_debug_comments']) {
		echo "\n";
		echo '<!-- CREATED CACHE @ ' . $cache . ' -->';
		echo "\n\n";
	}
	
	
	// We're done caching the current file.
	$launchpad_is_caching = false;
}


/**
 * Clear Post Part Cache
 *
 * @param		int|bool $post_id The post we need to clear caches about.
 * @since		1.0
 */
function launchpad_clear_cache($post_id) {
	global $post;
	
	// If no post id is set, we can't do anything.
	if(!$post_id) {
		return $post_id;
	}

	// Get the site cache folder.  Since we pass no params, it will be the folder.
	$cachefolder = launchpad_get_cache_file();
	
	// If there is a folder, we need to clean out anything related to that post.
	// That basically includes anything with the post's ID, the post's post type
	// of any archive.  We clear all archives because it's more efficient to 
	// clear all than detect which types need to be cleared for a specific post ID.
	if($cachefolder !== false && file_exists($cachefolder)) {
		// Open the folder.
		if($handle = opendir($cachefolder)) {
			
			// Loop the folder contents.
			while(false !== ($entry = readdir($handle))) {
				
				// Explode the path to make it easy to check IDs and such.
				$entry_parts = explode('-', $entry);
				
				// By default, assume we won't clear the cache file.
				$clear = false;
				
				// Do a series of gnarly checks to see if we need to clear it.
				// This used to be a single IF but it seemed like a solid
				// point of failure for future maintenances, so I reworked
				// it to be an easier-to-read series of conditionals.
				if($entry_parts[0] === 'launchpad_wpquery_cache') {
					$clear = true;
				} else if($entry_parts[0] === 'launchpad_db_cache') {
					$clear = true;
				} else if($entry_parts[0] === 'launchpad_post_cache') {
					if((int) $entry_parts[1] === (int) $post_id) {
						$clear = true;
					} else if($entry_parts[1] === 'archive') {
						$clear = true;
					} else if(isset($post->post_type)) {
						if($entry_parts[1] === $post->post_type) {
							$clear = true;
						}
					}
				}
				
				// If anything matches, delete it.
				if($clear) {
					unlink($cachefolder . $entry);
				}
			}
		}
	}
	return $post_id;
}
if(is_admin()) {
	add_action('save_post', 'launchpad_clear_cache');
}


/**
 * Clear All Caches
 *
 * @since		1.0
 */
function launchpad_clear_all_cache() {
	// Get the site cache folder.  Since we pass no params, it will be the folder.
	$cachefolder = launchpad_get_cache_file();
	
	// If there is a cache folder, we need to clear out anything designated as a post cache.
	if($cachefolder !== false && file_exists($cachefolder)) {
		// Open the folder.
		if($handle = opendir($cachefolder)) {
			
			// Loop the folder contents.
			while(false !== ($entry = readdir($handle))) {
				
				// If the first chunk is a post cache, delete the file.
				$entry_parts = explode('-', $entry);
				if(
					$entry_parts[0] === 'launchpad_post_cache' || 
					$entry_parts[0] === 'launchpad_wpquery_cache' ||
					$entry_parts[0] === 'launchpad_db_cache'
				) {
					unlink($cachefolder . $entry);
				}
			}
		}
	}
	return true;
}
if(is_admin()) {
	add_action('wp_update_nav_menu', 'launchpad_clear_all_cache');
}


/**
 * Get Template Part Passthru with Caching
 * 
 * @param		string|bool $slug The "slug" sent to get_template_part (e.g. content).
 * @param		string|bool $name The template part name to get. Typically a page name or post type.
 * @uses		launchpad_get_cache_id()
 * @uses		launchpad_cached()
 * @uses		launchpad_cache()
 * @since		1.0
 */
function launchpad_get_template_part($slug = false, $name = false) {
	
	// Get the cache ID for the currently queried object.
	$cache_id = launchpad_get_cache_id();
	
	// If no slug was specified, we can't get_template_part.
	if(!$slug) {
		return false;
	}
	
	// If we're in an admin area, avert caching.
	if(is_admin()) {
		if($name) {
			get_template_part($slug, $name);
		} else {
			get_template_part($slug);
		}
		return;
	}
	
	// Otherwise, try to get a cached file.
	// First, test the cache.  Testing the cache returns true or false based on
	// whether there was a valid cache.  If there was a valid cache, the cache
	// will have already been printed to the screen, so we do not need to do 
	// anything when a true value is returned.  Otherwise, output buffering has
	// started.  So, we can get the template part, then request to cache the
	// output from get_template_part via launchpad_cache.
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
 * @uses		launchpad_get_cache_id()
 * @uses		launchpad_cached()
 * @uses		launchpad_cache()
 * @since		1.0
 */
function launchpad_wp_nav_menu($args) {
	// Get the cache folder.
	$cache_id = launchpad_get_cache_id();
	// Create an ID for the navigation.
	$nav_id = md5(json_encode($args));
	
	// This works the same as the algorithm in launchpad_get_template_part().
	// Check there for details of how and why this works.
	if(!launchpad_cached($cache_id, 'navigation-' . $nav_id)) {
		wp_nav_menu($args);
		launchpad_cache($cache_id, 'navigation-' . $nav_id);
	}
}


/**
 * Cacheable WP_Query Stand In
 *
 * @since		1.0
 */
function LP_Query($query) {
	// Get the current cache file based on the post ID and type.
	$cache = launchpad_get_cache_file() . 'launchpad_wpquery_cache-' . md5(json_encode($query)) . '.cache';
	
	// If the cache file exists and hasn't expired, send the cached output to the browser.
	if(file_exists($cache) && time()-filemtime($cache) < USE_CACHE) {
		return unserialize(file_get_contents($cache));
	
	// Otherwise, run the query, cache it, and return the query.
	} else {
		$ret = new WP_Query($query);
		$f = @fopen($cache, 'w');
		if($f) {
			fwrite($f, serialize($ret));
			fclose($f);
		}
		return $ret;
	}
}
