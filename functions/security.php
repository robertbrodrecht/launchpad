<?php
/**
 * Security Related Features
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */


/**
 * Get the number of failures for a user.
 *
 * @param		string $username
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_get_failures($username) {
	global $launchpad_login_failures;
	
	$cache_path = launchpad_get_failures_cache($username);
	if(!file_exists($cache_path)) {
		$launchpad_login_failures = 0;
	} else {
		if(time()-filemtime($cache_path) > 3600) {
			unlink($cache_path);
			$launchpad_login_failures = 0;
		} else {
			$launchpad_login_failures = (int) file_get_contents($cache_path);
		}
	}
	return $launchpad_login_failures;
}


/**
 * Get the failure cache for a user.
 *
 * @param		string $username
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_get_failures_cache($username) {
	global $launchpad_login_failures_cache;

	$cache_folder = launchpad_get_cache_file();
	if(!file_exists($cache_folder)) {
		mkdir($cache_folder, 0777, true);
	}
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$file_name = 'limit-logins-' . sanitize_title($username) . '-' . $ip . '.txt';
	$cache_path = $cache_folder . $file_name;
	
	$launchpad_login_failures_cache = $cache_path;
	
	return $cache_path;
}


/**
 * Handle A Login Failure
 *
 * @param		string $username
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_login_failed($username) {
	global $site_options;
		
	$launchpad_login_failures = launchpad_get_failures($username);
	$launchpad_login_failures++;
	
	if($site_options['allowed_failures']-$launchpad_login_failures < 0) {
		return;
	}
	
	$f = fopen(launchpad_get_failures_cache($username), 'w');
	if($f) {
		fwrite($f, $launchpad_login_failures);
		fclose($f);
	}
}
add_action('wp_login_failed', 'launchpad_login_failed');


/**
 * Additional Login-attempt-based Authentication
 *
 * @param		object $user
 * @param		string $password
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_wp_authenticate_user($user, $password) {
	global $site_options;
	
	$launchpad_login_failures = launchpad_get_failures($user->data->user_login);
	
	$allowed_failures = $site_options['allowed_failures'];
	if(!$allowed_failures) {
		$allowed_failures = 10;
	}
	
	$lockout_time = $site_options['lockout_time'];
	if(!$lockout_time) {
		$lockout_time = 1;
	}
	
	$logins_left = ($allowed_failures-$launchpad_login_failures);
	
	if($logins_left < 1) {
		$logins_left = 0;
	}
	
	if(is_wp_error($user) || $logins_left > 0) {
		return $user;
	}
	
	$error = new WP_Error();
	$error->add('too_many_retries', 'Exhausted login attempts.');
	return $error;
}
add_filter('wp_authenticate_user', 'launchpad_wp_authenticate_user', 99999, 2);


/**
 * Add An Error
 *
 * I have no idea if this is required.
 * 
 * @param		object $error_codes
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_login_failure_shake($error_codes) {
	$error_codes[] = 'too_many_retries';
	return $error_codes;
}
add_filter('shake_error_codes', 'launchpad_login_failure_shake');


/**
 * Add messaging to the login screen.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_login_add_error_message() {
	global $error, $site_options, $launchpad_login_failures, $launchpad_login_failures_cache;
	
	if(!$launchpad_login_failures) {
		return;
	}
	
	$allowed_failures = $site_options['allowed_failures'];
	if(!$allowed_failures) {
		$allowed_failures = 10;
	}
	
	$lockout_time = $site_options['lockout_time'];
	if(!$lockout_time) {
		$lockout_time = 1;
	}
	
	$logins_left = ($allowed_failures-$launchpad_login_failures);
	
	if($logins_left < 1) {
		$logins_left = 0;
	}
	
	if($logins_left > 0) {
		$msg = '<br>You may try ' . $logins_left . ' more time' . ($logins_left !== 1 ? 's' : '') . ' before triggering a ' . $lockout_time . ' hour' . ($lockout_time !== 1 ? 's' : '') . ' lockout!';
		
		if($allowed_failures-$launchpad_login_failures < 3) {
			$msg .= '<br><strong>Please consider <a href="wp-login.php?action=lostpassword">resetting your password!</a></strong>';
		}
	} else {
		$lockout_elapsed = time() - filemtime($launchpad_login_failures_cache);
		$lockout_time_seconds = $lockout_time * 60 * 60;
		$lockout_time_left = ceil(($lockout_time_seconds-$lockout_elapsed)/60);
	
		$msg = '<br>You have been locked out.  You may try again in ' . $lockout_time_left . ' minutes.';
	}

	if ($msg != '') {
		$limit_login_my_error_shown = true;
		$error .= $msg;
	}
	return $error;
}
add_action('login_head', 'launchpad_login_add_error_message');


/**
 * Clear the Login Failures on Successful Login
 * 
 * @param		object $error_codes
 * @package 	Launchpad
 * @since   	Version 1.0
 */
function launchpad_clear_login_failures($user_login, $user) {
	$cache_file = launchpad_get_failures_cache($user_login);
	if(file_exists($cache_file)) {
		unlink($cache_file);
	}
}
add_action('wp_login', 'launchpad_clear_login_failures', 10, 2);