<?php

global $wpdb;

class LaunchpadDB extends wpdb {
	function get_results($query = null, $output = OBJECT) {
		$cache_time = 60;
		if(defined('USE_CACHE')) {
			$cache_time = USE_CACHE;
		}
		
		if(!stristr($query, 'SELECT') || stristr($query, 'ORDER BY RAND()')) {
			$cache_time = 0;
		}
		
		$cache = 'launchpad_db_cache-' . md5(serialize($query) . serialize($output)) . '.cache';
		$cache = sys_get_temp_dir() . '/' . md5($_SERVER['HTTP_HOST'])  . '/' . $cache;
		if(file_exists($cache) && time()-filemtime($cache) < $cache_time) {
			$return_val = unserialize(file_get_contents($cache));
		} else {
			$return_val = parent::get_results($query, $output);
			if($cache_time > 0) {
				$f = fopen($cache, 'w');
				fwrite($f, serialize($return_val));
				fclose($f);
			}
		}
		return $return_val;
	}
}

if(!is_admin()) {
	$wpdb = new LaunchpadDB(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}