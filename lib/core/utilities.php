<?php

/**
 * API
 *
 * Custom functions for handling various duties.
 *
 * @package 	Launchpad
 * @since		1.0
 */



/**
 * Format US, Canadian, and UK postal codes.
 * 
 * Formatting based on Wikipedia's suggested standards.  The following is a test:
 * 
 * <code>
 * $zips = array('35203', '35203-1234', 'K1A 0B1', 'EC1A 1BB', 'W1A 1HQ', 'M1 1AA', 'B33 8TH', 'CR2 6XH', 'DN55 1PT');
 * foreach($zips as $zip) {var_dump($zip == format_postal_code($zip));}
 * </code>
 * 
 * @link		http://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom
 * @param		string $zip The postal code.
 * @since		1.0
 */
function format_postal_code($zip = '') {
	
	// Remove any formatting that has been applied and upper case any letters.
	$zip = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $zip));
	
	// If the code is a US ZIP+4.
	if(preg_match('/^[\d]{9}$/', $zip)) {
		return substr($zip, 0, 5) . '-' . substr($zip, 5);
	
	// If the code is Canadian, format as "A9A 9A9"
	} else if(preg_match('/^[A-Z]\d[A-Z]\d[A-Z]\d$/', $zip)) {
		return substr($zip, 0, 3) . ' ' . substr($zip, 3);
	
	// If the code is a UK "AA9A 9AA"
	} else if(preg_match('/^[A-Z]{2}\d[A-Z]\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 4) . ' ' . substr($zip, 4);
		
	// If the code is a UK "A9A 9AA"
	} else if(preg_match('/^[A-Z]\d[A-Z]\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 3) . ' ' . substr($zip, 3);
		
	// If the code is a UK "A9 9AA"
	} else if(preg_match('/^[A-Z]\d\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 2) . ' ' . substr($zip, 2);
		
	// If the code is a UK "A99 9AA"
	} else if(preg_match('/^[A-Z]\d\d\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 3) . ' ' . substr($zip, 3);
		
	// If the code is a UK "AA9 9AA"
	} else if(preg_match('/^[A-Z]{2}\d\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 3) . ' ' . substr($zip, 3);
		
	// If the code is a UK "AA99 9AA"
	} else if(preg_match('/^[A-Z]{2}\d\d\d[A-Z]{2}$/', $zip)) {
		return substr($zip, 0, 4) . ' ' . substr($zip, 4);
	}
	
	// If we couldn't figure out a format or didn't need to format, return the zip.
	return $zip;
}


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
	
	// If there is no number, return an empty string.
	if($number === '') {
		return '';
	}
	
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


/**
 * Get a File (API Call) and Cache the Results
 *
 * @param		string $url Path to remote API.
 * @param		int $cachetime Time to cache.
 * @param		resource $context A stream context resource created with stream_context_create().
 * @since		1.0
 */
function file_get_contents_cache($url, $cachetime = 60, $context = false) {
	
	$system_temp = launchpad_temp_dir();
	
	// Get the site's temp folder.
	$cache_file = $system_temp . '/' . launchpad_site_unique_string();
	
	if(!file_exists($cache_file)) {
		@mkdir($cache_file, 0777);
	}
	
	// Append a hash of the URL.
	$cache_file = $cache_file . '/' . md5($url) . '.cache';
	
	// If the cache file doesn't exist or the file is older than $cachetime...
	if(!file_exists($cache_file) || time()-filemtime($cache_file) >= $cachetime) {
		
		// Fetch the file.
		$results = file_get_contents($url, false, ($context ? $context : null));
		
		// If there are results, write them to the cache file.
		if($results) {
			$f = @fopen($cache_file, 'w');
			if($f) {
				fwrite($f, $results);
				fclose($f);
			} else {
				return $results;
			}
		}
	}
	
	// Return the contents from the cache file.
	return file_get_contents($cache_file);
}


/**
 * Generate Pagination Automagically
 * 
 * This should work at least 80% of the time.  I hope.
 * 
 * @since	1.0
 * @uses	launchpad_paginate()
 */
function launchpad_auto_paginate($next = 'Next', $prev = 'Prev', $link_count = 5) {
	global $wp_query;
	
	// If we need pagination, add it.
	if($wp_query->max_num_pages > 1) {
		
		// Determine the current page.
		$current_page = get_query_var('paged');
		if($current_page == 0) {
			$current_page = 1;
		}
		
		// Determine the base URI for the page.
		$url_base = preg_replace(
				array(
					// Matches querystrings.
					'/\?.*?$/',
					// Matches page/#/
					'/\/page.*?$/'
				),
				'',
				$_SERVER['REQUEST_URI']
			);
		
		// Call the function to output the pagination.
		launchpad_paginate(
			$url_base, 
			$current_page, 
			$wp_query->max_num_pages, 
			array(
				'next' => $next,
				'previous' => $prev,
				'total_page_links' => $link_count
			)
		);
	}
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
	
	$qs = '';
	if($_GET) {
		$qs = '?' . http_build_query($_GET);
	}
	
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
			$ret .= '<li class="page-previous"><a href="' . $url_base . $qs . '">' . $previous . '</a></li>';
		} else {
			$ret .= '<li class="page-previous"><a href="' . $url_base . 'page/' . ($current_page-1) . '/' . $qs . '">' . $previous . '</a></li>';
		}
	} else {
		$ret .= '<li class="page-previous"><span>' . $previous . '</span></li>';
	}
	for(; $start_page <= $end_page; $start_page++) {
		if($start_page == $current_page) {
			$ret .= '<li class="page-number page-number-current"><span>' . $start_page . '</span></li>';							
		} else {
			if($start_page === 1) {
				$ret .= '<li class="page-number"><a href="' . $url_base . $qs . '">' . $start_page . '</a></li>';							
			} else {
				$ret .= '<li class="page-number"><a href="' . $url_base . 'page/' . $start_page . '/' . $qs . '">' . $start_page . '</a></li>';
			}
		}
	}
	if($current_page+1 <= $total_pages) {
		$ret .= '<li class="page-next"><a href="' . $url_base . 'page/' . ($current_page+1) . '/' . $qs . '">' . $next . '</a></li>';
	} else {
		$ret .= '<li class="page-next"><span>' . $next . '</span></li>';
	}
	$ret .= '</ul>';	
	
	if($echo) {
		echo $ret;
	}
	
	return $ret;
}


/**
 * Convert an Array to CSV
 * 
 * Piggybacks on temp file and fputcsv to convert an array to CSV.
 *
 * @param		array $data An array or array of arrays to convert to CSV.
 * @param		string $delimiter The character to use as the delimiter.
 * @param		string $enclosure What to wrap around strings with line breaks.
 * @since		1.5
 */
function array_to_csv($data, $delimiter = ',', $enclosure = '"') {
	if(!is_array($data)) {
		return false;
	}
	
	$handle = fopen('php://temp', 'r+');
	if(is_array($data[0])) {
		foreach ($data as $line) {
			fputcsv($handle, $line, $delimiter, $enclosure);
		}
	} else {
		fputcsv($handle, $data, $delimiter, $enclosure);
	}
	rewind($handle);
	
	$contents = '';
	while (!feof($handle)) {
		$contents .= fgets($handle);
	}
	fclose($handle);
	return $contents;
}


/**
 * Convert an Array to CSV
 * 
 * Piggybacks on temp file and fputcsv to convert an array to CSV.
 *
 * @param		array $data An array or array of arrays to convert to CSV.
 * @param		string $delimiter The character to use as the delimiter.
 * @param		string $enclosure What to wrap around strings with line breaks.
 * @since		1.5
 */
function csv_to_array($data, $delimiter = ',', $enclosure = '"') {
	$handle = fopen('php://temp', 'r+');
	fwrite($handle, $data);
	rewind($handle);
	$contents = array();
	while (!feof($handle)) {
		$contents[] = fgetcsv($handle, 0, $delimiter = ',', $enclosure = '"');
	}
	fclose($handle);
	if(count($contents) == 1) {
		$contents = $contents[0];
	}
	return $contents;
}


/**
 * Convert an PHP.ini Size to Bytes
 * 
 * Takes input like 1M and converts it to 1024.
 *
 * @param		array $data An array or array of arrays to convert to CSV.
 * @param		string $delimiter The character to use as the delimiter.
 * @param		string $enclosure What to wrap around strings with line breaks.
 * @since		1.5
 */
function parse_size($size) {
	// Remove the non-unit characters from the size.
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
	
	// Remove the non-numeric characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size);
	if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	} else {
		return round($size);
	}
}


/**
 * Return the singular or plural version based on the count.
 * 
 * @param		number|array $count The number of things.
 * @param		string $single The singular version of the string.
 * @param		string $plural The plural version of the string.
 * @returns		string
 * @since		1.6
 * @uses		launchpad_migrate_domain_replace
 */
function plural($count = 0, $single = '', $plural = false) {
	if(is_array($count)) {
		$count = count($count);
	}
	if($count == 1) {
		return $single;
	} else {
		return $plural === false ? $single . 's' : $plural;
	}
}
