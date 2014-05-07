<?php

/**
 * API
 *
 * Custom functions for handling various duties.
 *
 * @package 	Launchpad
 * @since		1.0
 * @todo		US / CA Postal Code Format
 * @todo		Validation: Phone, E-mail, US/CA Postal Code
 */


/**
 * Phone Number Formatting
 *
 * @since		1.0
 */
function format_phone($number = '', $mask = '(###) ###-####', $ext = ' x', $country = '+# ') {
	$return = '';
	$has_country_code = false;
	$number = preg_replace('/[^0-9]/', '', (string) $number);
	if(substr($number, 0, 1) === '1') {
		$number = substr($number, 1);
		$has_country_code = true;
	}
	$number = str_split($number);
	$extension = implode('', array_slice($number, substr_count($mask, '#')));
	
	$return = $mask;
	while(count($number) && stristr($return, '#') !== false) {
		$current_number = array_shift($number);
		$return = preg_replace('/\#/', $current_number, $return, 1);
	}
	
	if($extension) {
		$return = $return . $ext . $extension;
	}
	
	if($has_country_code) {
		$return = str_replace('#', '1', $country) . $return;
	}
	
	return $return;
}


/**
 * Use scandir for Recursive Directory Scanning
 *
 * @since		1.0
 */
function launchpad_scandir_deep($dir, $initial_dir = false) {
	if(substr($dir, -1) !== '/') {
		$dir = $dir . '/';
	}
	
	if(!$initial_dir) {
		$initial_dir = $dir;
	}
	
	$output = array();
	$files = scandir($dir);
	foreach($files as $file) {
		if(substr($file, 0, 1) !== '.') {
			if(is_dir($dir . $file)) {
				$output = array_merge(launchpad_scandir_deep($dir . $file, $initial_dir), $output);
			} else {
				$output[] = str_replace($initial_dir, '', $dir) . $file;
			}
		}
	}
	return $output;
}


/**
 * Get a File (API Call) and Cache the Results
 *
 * @param		string $url Path to remote API.
 * @param		int $cachetime Time to cache.
 * @since		1.0
 */
function file_get_contents_cache($url, $cachetime = 60) {
	$cache_file = sys_get_temp_dir();
	$cache_file = $cache_file . '/' . md5($url);
	if(!file_exists($cache_file) || time()-filemtime($cache_file) >= $cachetime) {
		$results = file_get_contents($url);
		if($results) {
			$f = fopen($cache_file, 'w');
			fwrite($f, $results);
			fclose($f);
		}
	}
	return file_get_contents($cache_file);
}


/**
 * Pagination Helper
 *
 * @param		string $url_base The base URL to add pagination links to.
 * @param		int $current_page The page the user is currently on.
 * @param		int $total_pages Total number of pages for the query.
 * @param		array $options An array with keys for total_page_links (number of direct page links), and next/previous for text.
 * @param		bool $echo Whether or not to print the nav to the page.
 * @since		1.0
 * @todo		Needs a lot more testing.
 */
function launchpad_paginate($url_base = '/', $current_page = 1, $total_pages = 1, $options = array(), $echo = true) {
	$ret = '';
	
	$defaults = array(
		'total_page_links' => 10, 
		'next' => 'Next',
		'previous' => 'Previous',
	);
	
	$settings = array_merge($defaults, $options);
	
	$next = $settings['next'];
	$previous = $settings['previous'];
	$total_page_links = $settings['total_page_links'];
	
	$half_total_page_links = round($total_page_links/2);
	
	if($current_page-5 < $half_total_page_links) {
		$start_page = 1;
	} else {
		$start_page = $current_page-$half_total_page_links;
	}
	
	if($current_page+$half_total_page_links > $total_pages) {
		$end_page = $total_pages;
	} else {
		$end_page = $current_page+$half_total_page_links;
	}
	if($end_page-$start_page < $total_page_links) {
		if($current_page-$half_total_page_links < 1) {
			if($end_page + ($total_page_links - ($end_page-$start_page)) < $total_pages) {
				$end_page += ($total_page_links - ($end_page-$start_page));
			}
		} else if($current_page+$half_total_page_links > $total_pages) {
			if($start_page - ($total_page_links - ($end_page-$start_page)) > 0) {
				$start_page -= ($total_page_links - ($end_page-$start_page));
			}
		}
	}

	$ret .= '<ul class="page-navigate">';
	if($current_page-1 > 0) {
		if($current_page-1 === 1) {
			$ret .= '<li class="page-previous"><a href="' . $url_base . '">' . $previous . '</a></li>';
		} else {
			$ret .= '<li class="page-previous"><a href="' . $url_base . 'page/' . ($current_page-1) . '/">' . $previous . '</a></li>';
		}
	} else {
		$ret .= '<li class="page-previous"></li>';
	}
	for(; $start_page <= $end_page; $start_page++) {
		if($start_page == $current_page) {
			$ret .= '<li class="page-number page-number-current"><span>' . $start_page . '</span></li>';							
		} else {
			if($start_page === 1) {
				$ret .= '<li class="page-number"><a href="' . $url_base . '">' . $start_page . '</a></li>';							
			} else {
				$ret .= '<li class="page-number"><a href="' . $url_base . 'page/' . $start_page . '/">' . $start_page . '</a></li>';
			}
		}
	}
	if($current_page+1 <= $total_pages) {
		$ret .= '<li class="page-next"><a href="' . $url_base . 'page/' . ($current_page+1) . '/">' . $next . '</a></li>';
	} else {
		$ret .= '<li class="page-next"></li>';
	}
	$ret .= '</ul>';	
	
	if($echo) {
		echo $ret;
	}
	
	return $ret;
}