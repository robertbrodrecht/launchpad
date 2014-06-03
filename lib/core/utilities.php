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
 * Format a US phone number containing ONLY numbers.
 *
 * @param		string $number The phone number to format.
 * @param		string $mask The output mask to use. Just put pound signs where numbers go.
 * @param		string $ext The extension separator. Once all pound signs are replaces, append as the extension.
 * @param		string $country How to format the country code, if the first number is a 1.
 * @since		1.0
 */
function format_phone($number = '', $mask = '(###) ###-####', $ext = ' x', $country = '+# ') {
	
	// This will hold the formatted phone number by replacing each # with a number.
	$return = $mask;
	
	// True if a country code is found, otherwise false.
	$has_country_code = false;
	
	// Remove all non-digit characters.
	$number = preg_replace('/[^0-9]/', '', (string) $number);
	
	// Check whether the phone number starts with a 1.
	// If so, get it and set the country code flag.
	if(substr($number, 0, 1) === '1') {
		
		// Remove the "1" off of the front.
		$number = substr($number, 1);
		$has_country_code = true;
	}
	
	// Turn the number into an array.
	$number = str_split($number);
	
	// Reduce the array to just the extension by slicing the 
	// array after the number of # signs that are in the mask.
	$extension = implode('', array_slice($number, substr_count($mask, '#')));
	
	// Loop the numbers that are left as long as the return has a # left in it.
	while(count($number) && stristr($return, '#') !== false) {
		
		// Remove the current number from the front.
		$current_number = array_shift($number);
		
		// Replace one instance of # with the current number.
		$return = preg_replace('/\#/', $current_number, $return, 1);
	}
	
	// If there is an extension, add the extnesion separator and the extension.
	if($extension) {
		$return = $return . $ext . $extension;
	}
	
	// If there is a country code, handle the replace and add it to the output.
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
	
	// If the path doesn't end with a slash, add one.
	if(substr($dir, -1) !== '/') {
		$dir = $dir . '/';
	}
	
	// If no initial folder is set, set it to the current folder.
	if(!$initial_dir) {
		$initial_dir = $dir;
	}
	
	// Create an array to handle the output.
	$output = array();
	
	// Scandir the folder, creating an array of the file list.
	$files = scandir($dir);
	
	// Loop the file list.
	foreach($files as $file) {
		
		// If the file is not a hidden file or the standard POSIX . / .., we want to keep a record.
		if(substr($file, 0, 1) !== '.') {
			
			// If it is a folder, merge the current $output with a recursive list of that folder's output.
			if(is_dir($dir . $file)) {
				$output = array_merge(launchpad_scandir_deep($dir . $file, $initial_dir), $output);
			
			// Otherwise, make the path relative to the inital folder.
			} else {
				$output[] = str_replace($initial_dir, '', $dir) . $file;
			}
		}
	}
	
	return $output;
}
file_get_contents_cache('http://vodkabuzz.dev/');

/**
 * Get a File (API Call) and Cache the Results
 *
 * @param		string $url Path to remote API.
 * @param		int $cachetime Time to cache.
 * @since		1.0
 */
function file_get_contents_cache($url, $cachetime = 60) {
	
	// Get the site's temp folder.
	$cache_file = sys_get_temp_dir() . '/' . launchpad_site_unique_string();
	
	// Append a hash of the URL.
	$cache_file = $cache_file . '/' . md5($url) . '.cache';
	
	// If the cache file doesn't exist or the file is older than $cachetime...
	if(!file_exists($cache_file) || time()-filemtime($cache_file) >= $cachetime) {
		
		// Fetch the file.
		$results = file_get_contents($url);
		
		// If there are results, write them to the cache file.
		if($results) {
			$f = fopen($cache_file, 'w');
			fwrite($f, $results);
			fclose($f);
		}
	}
	
	// Return the contents from the cache file.
	return file_get_contents($cache_file);
}


/**
 * Pagination Helper
 * 
 * Don't use this unless you are testing it!!!  It's only been used on one site,
 * and it's custom-tailored for that site.  It may not be flexible enough.
 * I need to write some test cases.  That is very low on my list.
 *
 * @param		string $url_base The base URL to add pagination links to.
 * @param		int $current_page The page the user is currently on.
 * @param		int $total_pages Total number of pages for the query.
 * @param		array $options An array with keys for total_page_links (number of direct page links), and next/previous for text.
 * @param		bool $echo Whether or not to print the nav to the page.
 * @since		1.0
 * @ignore		This function may be deprecated soon or removed without warning.
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