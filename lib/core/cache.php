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
 * 
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
	
	if(!file_exists(sys_get_temp_dir() . '/' . $site_unique_string  . '/')) {
		mkdir(sys_get_temp_dir() . '/' . $site_unique_string  . '/', 0777, true);
	}
	
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



/**
 * Generate an App Cache Manifest
 *
 * @since		1.0
 */
function launchpad_cache_manifest() {
	$site_options = get_option('launchpad_site_options', '');
	
	$file_max_size = 256000;
	$cache_max_size = 52428800;
	
	$total_cache_size = 0;
	
	$file_list = array();
	$latest = filemtime(launchpad_get_cache_file());
	
	// Locations for images and CSS.
	$paths = array(
			'/' . THEME_PATH . '/css/' => '/css/',
			'/' . THEME_PATH . '/js/' => '/js/',
			'/' . THEME_PATH . '/images/' => '/images/'
		);
		
	$paths = apply_filters('launchpad_cache_manifest_file_paths', $paths);
	
	// Load all the images and CSS.
	foreach($paths as $path => $rewrite_path) {
		$path_local = $_SERVER['DOCUMENT_ROOT'] . $path;
		if($rewrite_path === '/images/') {
			$files = scandir($path_local);			
		} else {
			$files = launchpad_scandir_deep($path_local);
		}
		if($files) {
			foreach($files as $file) {
				$file_cache_size = filesize($path_local . $file);
				
				if(
					substr($file, 0, 1) !== '.' && 
					!is_dir($path_local . $file) && 
					!preg_match('/.*\.(psd|map)$/', $file) &&
					$file_cache_size <= $file_max_size &&
					$total_cache_size+$file_cache_size < $cache_max_size
				) {
					$file_list[] = $rewrite_path . $file;
					$total_cache_size += $file_cache_size;
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
	$post_types[] = 'page';
	
	// Query for all custom post types and pages.
	$q = new WP_Query(
			array(
				'post_type' => $post_types,
				'posts_per_page' => -1
			)
		);
	foreach($q->posts as $p) {
		$pl = get_permalink($p->ID);
		
		// Get the assets out of the page.
		$output = file_get_contents($pl);
		$file_cache_size = strlen($output);
		
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			$file_list[] = $pl;
			$total_cache_size += $file_cache_size;
		
			preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
			if($matches[1]) {
				foreach($matches[1] as $asset_path) {
					if(substr($asset_path, 0, 2) === '//') {
						$asset_path = 'http:' . $asset_path;
					}
					if(!in_array($asset_path, $file_list)) {
						if(substr($asset_path, 0, 1) === '/') {
							$file_cache_size = filesize($_SERVER['DOCUMENT_ROOT'] . $asset_path);
						} else {
							$file_cache_size = strlen(file_get_contents($asset_path));
						}
						
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
		}
		
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
		if(strtotime($p->post_modified) > $latest) {
			$latest = strtotime($p->post_date);
		}
	}
	
	// Get 100 posts.
	$q = new WP_Query(
			array(
				'post_type' => 'post',
				'posts_per_page' => 100
			)
		);
	foreach($q->posts as $p) {
		$pl = get_permalink($p->ID);
		$output = file_get_contents($pl);
		$file_cache_size = strlen($output);
		
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			$file_list[] = $pl;
			$total_cache_size += $file_cache_size;
			
			// Get the assets out of the post.
			$output = file_get_contents($pl);
			preg_match_all('/src=[\'\"](.*?)[\'\"]/', $output, $matches);
			if($matches[1]) {
				foreach($matches[1] as $asset_path) {
					if(substr($asset_path, 0, 2) === '//') {
						$asset_path = 'http:' . $asset_path;
					}				
					if(!in_array($asset_path, $file_list)) {
						if(substr($asset_path, 0, 1) === '/') {
							$file_cache_size = filesize($_SERVER['DOCUMENT_ROOT'] . $asset_path);
						} else {
							$file_cache_size = strlen(file_get_contents($asset_path));
						}
						
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
		$posts_page = get_permalink($posts_page);
		
		$output = file_get_contents($posts_page);
		$file_cache_size = strlen($output);
		if(
			$file_cache_size && 
			$file_cache_size <= $file_max_size &&
			$total_cache_size+$file_cache_size < $cache_max_size
		) {
			$file_list[] = $posts_page;
			$total_pages = ceil($q->found_posts/get_option('posts_per_page'));
			for($i = 2; $i < $total_pages; $i++) {
				$tmp_pl = $posts_page . 'page/' . $i . '/';
				
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
	
	sort($file_list);
	
	$total_cache_size_bytes = '';
	
	$size = 'bytes';
	$sizes = array('kilobytes', 'megabytes');
	if($total_cache_size/1024 > 0) {
		$total_cache_size_bytes = '(' . $total_cache_size . ' bytes)';
		while($total_cache_size/1024 > 1 && $sizes) {
			$size = array_shift($sizes);
			$total_cache_size = $total_cache_size / 1024;
		}
	}
	
	$total_cache_size = number_format($total_cache_size, 3);
	
	//header('Content-type: text/plain'); // Use this for debugging.
	header('Content-type: text/cache-manifest');
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime('+1 second')));
	
	echo "CACHE MANIFEST\n\n";
	echo "# Last Modified: " . date('Y-m-d H:i:s T', $latest) . " \n";
	echo "# Total Cache Size: $total_cache_size $size $total_cache_size_bytes \n\n";
	
	echo "CACHE:\n";
	echo implode("\n", array_unique($file_list));
	echo "\n\n";
	
	echo "NETWORK:\n*\n\n";
	echo "FALLBACK:\n";
	echo "/uploads/ /support/offline.png\n";
	echo "/images/ /support/offline.png\n";
	echo "/img/ /support/offline.png\n";
	echo "/ /support/offline.html\n";
	exit;
}
add_action('wp_ajax_cache_manifest', 'launchpad_cache_manifest');
add_action('wp_ajax_nopriv_cache_manifest', 'launchpad_cache_manifest');


/**
 * Obsolete an App Cache Manifest
 *
 * @since		1.0
 */
function launchpad_cache_manifest_obsolete() {
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime('-1 month')));
	http_response_code(404);
	exit;
}
add_action('wp_ajax_cache_manifest_obsolete', 'launchpad_cache_manifest_obsolete');
add_action('wp_ajax_nopriv_cache_manifest_obsolete', 'launchpad_cache_manifest_obsolete');
