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
	if(!file_exists($cache_file) && time()-filemtime($cache_file) >= $cachetime) {
		$results = file_get_contents($url);
		if($results) {
			$f = fopen($cache_file, 'w');
			fwrite($f, $results);
			fclose($f);
		}
	}
	return file_get_contents($cache_file);
}