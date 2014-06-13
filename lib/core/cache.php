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
	return md5($_SERVER['HTTP_HOST']);
}


/**
 * Get Current Cache ID
 *
 * @since		1.0
 */
function launchpad_get_cache_id() {
	global $post, $wp_query;
	
	// If the post is singular, the cache id is the post id.
	if($wp_query->is_singular || $wp_query->is_single) {
		$cache_id = $post->ID;
	
	// If we're on an archive page, get the the taxonomy and slug as the cache id.
	} else if($wp_query->is_archive) {
		$cur_tax = $wp_query->get_queried_object();
		$cache_id = 'archive-' . $cur_tax->taxonomy . '-' . $cur_tax->slug;
	
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
	if(!USE_CACHE) {
		return false;
	}
	
	// Get the site's unique string.
	$site_unique_string = launchpad_site_unique_string();
	
	// Create the site cache folder if it doesn't exist.
	if(!file_exists(sys_get_temp_dir() . '/' . $site_unique_string  . '/')) {
		mkdir(sys_get_temp_dir() . '/' . $site_unique_string  . '/', 0777, true);
	}
	
	// If the cache file is for a post but not a type, set a file name with just the ID.
	if($post_id && !$type) {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string . '/launchpad_post_cache-' . $post_id . '-file.html';
	
	// If there is a post and type, set a file name with both ID and type.
	} else if($post_id && $type) {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string  . '/launchpad_post_cache-' . $post_id . '-' . $type . '-file.html';
	
	// Otherwise, just return the folder.
	} else {
		$cache = sys_get_temp_dir() . '/' . $site_unique_string  . '/';
	}
	
	// Apply filters to allow the developer to modify the value.
	$cache = apply_filters('launchpad_cache_file_path', $cache, $post_id, $type);
	
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
	global $site_options;
	
	// If cache is disabled or the user is logged in, we don't cache.
	if(!USE_CACHE || is_user_logged_in()) {
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- Cache disabled because ' . (!USE_CACHE ? 'caching is disabled.' : 'you are logged in.') . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	// Get the current cache file based on the post ID and type.
	$cache = launchpad_get_cache_file($post_id, $type);
	
	// If the cache file exists and hasn't expired, send the cached output to the browser.
	if(file_exists($cache) && time()-filemtime($cache) < USE_CACHE) {
		readfile($cache);
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- USED ' . (time()-filemtime($cache)) . ' SECOND OLD CACHE @ ' . $cache . ' -->';
			echo "\n\n";
		}
		return true;
	
	// Otherwise, we need to turn on output buffering so we can cache the output.
	} else {
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
	global $site_options;
	
	// If cache is disabled or the user is logged in, we don't cache.
	if(!USE_CACHE || is_user_logged_in()) {
		if($site_options['cache_debug_comments']) {
			echo "\n";
			echo '<!-- Not generating cache because ' . (!USE_CACHE ? 'caching is disabled.' : 'you are logged in.') . ' -->';
			echo "\n\n";
		}
		return false;
	}
	
	// If we get here, that means launchpad_cached() should have enabled output buffering.
	// Grab the output buffer's contents.
	$cache_content = ob_get_contents();
	
	// Get the site cache folder.  Since we pass no params, it will be the folder.
	$cache_folder = launchpad_get_cache_file();
	if(!file_exists($cache_folder)) {
		mkdir($cache_folder, 0777, true);
	}
	
	// Get the cache file for the current post and type.
	$cache = launchpad_get_cache_file($post_id, $type);
	
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
	if(file_exists($cachefolder)) {
		// Open the folder.
		if($handle = opendir($cachefolder)) {
			
			// Loop the folder contents.
			while(false !== ($entry = readdir($handle))) {
				
				// Explode the path to make it easy to check IDs and such.
				$entry_parts = explode('-', $entry);
				
				// If anything matches, delete it.
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
	if(file_exists($cachefolder)) {
		// Open the folder.
		if($handle = opendir($cachefolder)) {
			
			// Loop the folder contents.
			while(false !== ($entry = readdir($handle))) {
				
				// If the first chunk is a post cache, delete the file.
				$entry_parts = explode('-', $entry);
				if($entry_parts[0] === 'launchpad_post_cache') {
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
 * Generate an App Cache Manifest
 *
 * @since		1.0
 */
function launchpad_cache_manifest() {
	
	// Get the site options.
	$site_options = get_option('launchpad_site_options', '');
	
	// The maximum file size to include in the cache is 250K
	$file_max_size = 256000;
	
	// The maximum cache size is 50M.
	$cache_max_size = 52428800;
	
	// Running total for the cache size.
	$total_cache_size = 0;
	
	// An array to store the list of included files in.
	// This is used to help prevent duplication versus appending to a string.
	$file_list = array();
	
	// The latest mod time, by default, is the site cache folder's mod time.
	// This allows us to force appCache to update whenever we want by saving
	// the launchpad settings.  Since saving touches the cache folder, the
	// next time appCache checks for updates (page load and after an ajax call)
	// The cache manifest will be different (because we output the latest time
	// in the file).  Otherwise, this value will match the most recent modtime
	// from all of the included posts.
	$latest = filemtime(launchpad_get_cache_file());
	
	// Locations for images and CSS.
	$paths = array(
			THEME_PATH . '/css/' => '/css/',
			THEME_PATH . '/js/' => '/js/',
			THEME_PATH . '/images/' => '/images/'
		);
	
	// Apply filters so the developer can change any values.
	// This may not be enough given /img/ versus /images/.
	$paths = apply_filters('launchpad_cache_manifest_file_paths', $paths);
	
	// Load all the images and CSS.
	// Loop each path to check.
	foreach($paths as $path => $rewrite_path) {
		
		// The path local is the full, non-rewrite path to the folder.
		$path_local = $_SERVER['DOCUMENT_ROOT'] . $path;
		
		// If the path is images, we only want top-level images.
		// This avoids caching all icon assets, etc.
		if($rewrite_path === '/images/' || $rewrite_path === '/img/') {
			$files = scandir($path_local);
		// Otherwise, we want to make sure we cache everything for css, js, etc.
		} else {
			$files = launchpad_scandir_deep($path_local);
		}
		
		// If we have any files, we need to include them.
		if($files) {
			
			// Loop the file listing.
			foreach($files as $file) {
				
				// Get the file size of the current file.
				$file_cache_size = filesize($path_local . $file);
				
				// If the file is not hidden, not a folder, not a PSD or map, 
				// will fit within the allowed cache size and is less than the
				// max size per file, we want to include the file in the cache.
				if(
					substr($file, 0, 1) !== '.' && 
					!is_dir($path_local . $file) && 
					!preg_match('/.*\.(psd|map)$/', $file) &&
					$file_cache_size <= $file_max_size &&
					$total_cache_size+$file_cache_size < $cache_max_size
				) {
					// Add to the list.
					$file_list[] = $rewrite_path . $file;
					// Update the total cache size.
					$total_cache_size += $file_cache_size;
					// If the file mod time is newer than the latest mod time,
					// update the latest time.
					if(filemtime($path_local . $file) > $latest) {
						$latest = filemtime($path_local . $file);
					}
				}
			}
		}
	}
	
	// Get the list of custom post types.
	$post_types = get_post_types(
			array(
				'public' => true,
				'publicly_queryable' => true,
				'_builtin' => false
			)
		);
		
	$post_types = array_values($post_types);
	
	// We also want to include "page" post types here.
	// We will include "post" post types later.
	$post_types[] = 'page';
	
	// Query for all custom post types and pages.
	$q = new WP_Query(
			array(
				'post_type' => $post_types,
				'posts_per_page' => -1
			)
		);
	
	// Loop the posts.
	foreach($q->posts as $p) {
		// The permalink to the file.
		$pl = get_permalink($p->ID);
		
		// Get the page content so we can get the assets out of the page.
		$output = file_get_contents($pl);
		
		// Get the file size of the page.
		$file_cache_size = strlen($output);
		
		// If the page has content, is less than the max file size, and will fit
		// in the cahce, add it to the cache.
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			// Add the permalink to the file list.
			$file_list[] = $pl;
			// Update the total cache size.
			$total_cache_size += $file_cache_size;
			
			// Find all included files (i.e. images, scripts).
			preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
			
			// If there are files to include, try adding them.
			if($matches[1]) {
				// Loop the URL list.
				foreach($matches[1] as $asset_path) {
					// If the asset starts with '//', add http:.
					if(substr($asset_path, 0, 2) === '//') {
						$asset_path = 'http:' . $asset_path;
					}
					
					// If we haven't already cached the asset, see about caching it.
					if(!in_array($asset_path, $file_list)) {
						// If the file is local, check its file size.
						if(substr($asset_path, 0, 1) === '/') {
							$file_cache_size = filesize($_SERVER['DOCUMENT_ROOT'] . $asset_path);
						
						// If not, use the length of the file as the file size.
						} else {
							$file_cache_size = strlen(file_get_contents($asset_path));
						}
						
						// If the file has length, is less than the max size, and will fit
						// in the cahce, add it to the cache.
						if(
							$file_cache_size && 
							$file_cache_size <= $file_max_size &&
							$total_cache_size+$file_cache_size < $cache_max_size
						) {
							// Update the file list.
							$file_list[] = $asset_path;
							// Update the total cache size.
							$total_cache_size += $file_cache_size;
						}
					}
				}
			}
		}
		
		// Next, try to add down-stream pages from the current permalink.
		// Break apart the permalink.
		$pl = explode('/', $pl);
		// Remove the last value.  It should be empty.
		array_pop($pl);
		// While there are more than 3 items (that is, we are above the domain name)
		// loop the array.
		while(count($pl) > 3) {
			// Pop the last value off the array.
			array_pop($pl);
			// Join the array into a temporary 
			$tmp_pl = implode('/', $pl) . '/';
			// If we have not cached that URL, try to cache it.
			if(!in_array($tmp_pl, $file_list)) {
				// This should look familiar.
				// If not, check the previous block for details.
				$output = file_get_contents($tmp_pl);
				$file_cache_size = strlen($output);
				if(
					$file_cache_size && 
					$file_cache_size <= $file_max_size &&
					$total_cache_size+$file_cache_size < $cache_max_size
				) {
					$file_list[] = $tmp_pl;
					$total_cache_size += $file_cache_size;
				}
			}
		}
		// If the mod time on the post is newer than latest, update the latest mod time.
		if(strtotime($p->post_modified) > $latest) {
			$latest = strtotime($p->post_date);
		}
	}
	
	// Now we deal with posts.
	// Get 100 posts.
	$q = new WP_Query(
			array(
				'post_type' => 'post',
				'posts_per_page' => 100
			)
		);
	
	// Loop the posts.
	foreach($q->posts as $p) {
		// Get the permalink and it's cache size.
		$pl = get_permalink($p->ID);
		$output = file_get_contents($pl);
		$file_cache_size = strlen($output);
		
		// If there is content, it's under the max file size, and fits in the cache,
		// we need to include it.
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			
			// Update the list and cache size.
			$file_list[] = $pl;
			$total_cache_size += $file_cache_size;
			
			// Get the assets out of the post.
			$output = file_get_contents($pl);
			
			// Find all included files (i.e. images, scripts).
			preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
			if($matches[1]) {
				
				// Loop them.
				foreach($matches[1] as $asset_path) {
					
					// Prepend http: if needed.
					if(substr($asset_path, 0, 2) === '//') {
						$asset_path = 'http:' . $asset_path;
					}
					
					// If we haven't already cached...
					if(!in_array($asset_path, $file_list)) {
						
						// Try to figure out the file size (same as above).
						if(substr($asset_path, 0, 1) === '/') {
							$file_cache_size = filesize($_SERVER['DOCUMENT_ROOT'] . $asset_path);
						} else {
							$file_cache_size = strlen(file_get_contents($asset_path));
						}
						
						// If the file has a size, is less than the max, and fits in the cache, 
						// add it to the cache (same as above).
						if(
							$file_cache_size && 
							$file_cache_size <= $file_max_size &&
							$total_cache_size+$file_cache_size < $cache_max_size
						) {
							$file_list[] = $asset_path;
							$total_cache_size += $file_cache_size;
						}
					}
				}
			}
			
			// Just like with pages, try to grab down-level pages.
			// This would be, e.g., an archive page.
			$pl = explode('/', $pl);
			array_pop($pl);
			while(count($pl) > 3) {
				array_pop($pl);
				$tmp_pl = implode('/', $pl) . '/';
				if(!in_array($tmp_pl, $file_list)) {
					$output = file_get_contents($tmp_pl);
					$file_cache_size = strlen($output);
					if(
						$file_cache_size && 
						$file_cache_size <= $file_max_size &&
						$total_cache_size+$file_cache_size < $cache_max_size
					) {
						$file_list[] = $tmp_pl;
						$total_cache_size += $file_cache_size;
					}
				}
			}
		}
		if(strtotime($p->post_modified) > $latest) {
			$latest = strtotime($p->post_date);
		}
	}
	
	// Get enough archive pages to support the posts we got.
	$posts_page = get_option('page_for_posts');
	if($posts_page) {
		// Get the posts page permalink.
		$posts_page = get_permalink($posts_page);
		
		// Get the file size.
		$output = file_get_contents($posts_page);
		$file_cache_size = strlen($output);
		
		// If it is cachable (see above if you still don't get how this conditional works)...
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			// Add it to the cache.
			$file_list[] = $posts_page;
			
			// Figure out if there are going to be multiple pages.
			// By doing this, we avoid 404 URLS going into the cache.
			$total_pages = ceil($q->found_posts/get_option('posts_per_page'));
			
			// While there are pages, loop the pages to add to the cache.
			for($i = 2; $i < $total_pages; $i++) {
				// Generate a permalink to the page.
				$tmp_pl = $posts_page . 'page/' . $i . '/';
				
				// Get the size.
				$output = file_get_contents($tmp_pl);
				$file_cache_size = strlen($output);
				
				// If it is cachable, add it to the cache.
				if(
					$file_cache_size && 
					$file_cache_size <= $file_max_size &&
					$total_cache_size+$file_cache_size < $cache_max_size
				) {
					$file_list[] = $tmp_pl;
					$total_cache_size += $file_cache_size;
				}
			}
		}
	}
	
	// Sort the file list.
	sort($file_list);
	
	// Cache size in bytes will go here as a comment.
	$total_cache_size_bytes = '';
	
	// The default size is in bytes.
	$size = 'bytes';
	// The next two orders up from bytes.
	$sizes = array('kilobytes', 'megabytes');
	
	// This tries to make a human-readable cache size.
	// While we can divide by 1024, there is another order above the current order.
	// Since our max size is 50 megs, we'll only ever show megabytes at worst.
	if($total_cache_size/1024 > 0) {
		// Save the cache size in bytes to display as a note.
		// If the total cache is under 1K, the output will be presented in bytes,
		// making this superfluous.  So, we only set a value if we are dealing 
		// with kilobyte+ cache sizes.
		$total_cache_size_bytes = '(' . $total_cache_size . ' bytes)';
		
		// Loop while we can divide by 1024.
		while($total_cache_size/1024 > 1 && $sizes) {
			// Shift off the first value to correspond with the current size.
			$size = array_shift($sizes);
			// Divide the total cache by 1024 to get the current size in the current units.
			$total_cache_size = $total_cache_size / 1024;
		}
	}
	
	// Format the cache size.
	$total_cache_size = number_format($total_cache_size, 3);
	
	//header('Content-type: text/plain'); // Use this for debugging.
	// This is the corrent content-type for a cache manifest.
	header('Content-type: text/cache-manifest');
	
	// Set to expire immediately to avoid excessive caching.
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime('+1 second')));
	
	// The required header.
	echo "CACHE MANIFEST\n\n";
	
	// These lines are used to help invalidate the cache.
	// AppCache invalidates ONLY if the manifest has changed.
	// So, we print the last mod time of the most recently modified file
	// AND we print the total cahce size.
	// If either of those change, appCache is supposed to update because the manifest
	// will be different.
	echo "# Last Modified: " . date('Y-m-d H:i:s T', $latest) . " \n";
	echo "# Total Cache Size: $total_cache_size $size $total_cache_size_bytes \n\n";
	
	// These are the files to cache explicitly.
	echo "CACHE:\n";
	echo implode("\n", array_unique($file_list));
	echo "\n\n";
	
	// Anything else goes to the network.
	echo "NETWORK:\n*\n\n";
	
	// If we are offline, we may need some fallbacks.
	// The fallbacks aren't file-type based, unfortunately.
	// So, if the fallback is for places commonly used to store
	// images, use an image.  Otherwise, use an HTML fallback.
	echo "FALLBACK:\n";
	echo "/uploads/ /support/offline.png\n";
	echo "/images/ /support/offline.png\n";
	echo "/img/ /support/offline.png\n";
	echo "/ /support/offline.html\n";
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_cache_manifest', 'launchpad_cache_manifest');
	add_action('wp_ajax_nopriv_cache_manifest', 'launchpad_cache_manifest');
}
