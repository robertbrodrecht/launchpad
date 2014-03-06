<?php

/**
 * API
 *
 * Custom functions for handling various duties.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */


/**
 * Phone Number Formatting
 *
 * @since   	Version 1.0
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
