<?php
/**
 * Security Related Features
 *
 * Lockout code inspired by the Limit Login Attempts plugin.  This ended up being a 
 * fairly convoluted thing to pull off.
 * 
 * Lockouts are per-username and per-IP.  That means your co-worker can't lock you out.
 * Lockout counts are cleared when the user successfully logs in or when settings are saved.
 * 
 * @package 	Launchpad
 * @since		1.0
 */



/**
 * Get the failure cache for a user.
 *
 * @param		string $username
 * @uses		launchpad_get_cache_file()
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_get_failures_cache($username) {
	global $launchpad_login_failures_cache;
	
	// Get the site cache folder.
	$cache_folder = launchpad_get_cache_file();
	
	// If it doesn't exist, create it.
	if(!file_exists($cache_folder)) {
		mkdir($cache_folder, 0777, true);
	}
	
	// Generate a cache file name based on the username and IP.
	$ip = $_SERVER['REMOTE_ADDR'];
	$file_name = 'launchpad_limit_logins-' . sanitize_title($username) . '-' . $ip . '.txt';
	$cache_path = $cache_folder . $file_name;
	
	// This is a global variable that is used in the same script.
	// Unfortunately, the hook we need to check $launchpad_login_failures_cache
	// in does not pass a username.  So, we have to set this globally to access
	// it in the hook without the username.
	$launchpad_login_failures_cache = $cache_path;
	
	return $cache_path;
}


/**
 * Get the number of failures for a user.
 *
 * @param		string $username
 * @uses		launchpad_get_failures_cache()
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_get_failures($username) {
	global $launchpad_login_failures, $site_options;
	
	// Local value for lockout time in hours.
	$lockout_time = $site_options['lockout_time'];
	if(!$lockout_time) {
		$lockout_time = 1;
	}
	
	// Get the failures cache for the current username.
	$cache_path = launchpad_get_failures_cache($username);
	
	// If the file does not exist, the number of failures is zero.
	if(!file_exists($cache_path)) {
		$launchpad_login_failures = 0;
	
	// If the file does exist, the number of failures varies.
	} else {
	
		// If the lockout cache has expired, the number of failures is zero.
		if(time()-filemtime($cache_path) > $lockout_time*60*60) {
			unlink($cache_path);
			$launchpad_login_failures = 0;
			
		// If not, there will be a value in the file that represents the number of failures.
		} else {
			$launchpad_login_failures = (int) file_get_contents($cache_path);
		}
	}
	
	return $launchpad_login_failures;
}


/**
 * Handle A Login Failure
 * 
 * This function only runs when a login fails.
 *
 * @param		string $username
 * @uses		launchpad_get_failures()
 * @uses		launchpad_get_failures_cache()
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_login_failed($username) {
	global $site_options;
		
	// Get the current login failure count.
	$launchpad_login_failures = launchpad_get_failures($username);
	
	// Add one since this will only occur when the login fails.
	$launchpad_login_failures++;
	
	// If the user has exhausted the number of login failures, don't bother logging it.
	if($site_options['allowed_failures']-$launchpad_login_failures < 0) {
		return;
	}
	
	// Otherwise, update the login failure count for the user.
	$f = fopen(launchpad_get_failures_cache($username), 'w');
	if($f) {
		fwrite($f, $launchpad_login_failures);
		fclose($f);
	}
}
if($GLOBALS['pagenow'] === 'wp-login.php') {
	add_action('wp_login_failed', 'launchpad_login_failed');
}


/**
 * Add An Error
 *
 * Adds a "too many retries" error to the shake error codes.
 * 
 * @param		object $error_codes
 * @see			launchpad_wp_authenticate_user()
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_login_failure_shake($error_codes) {
	$error_codes[] = 'too_many_retries';
	return $error_codes;
}
if($GLOBALS['pagenow'] === 'wp-login.php') {
	add_filter('shake_error_codes', 'launchpad_login_failure_shake');
}


/**
 * Additional Login-attempt-based Authentication
 * 
 * This should only fire if a user has entered a successful login.
 * We need this in the event the hacker has used up all attempts,
 * keeps trying to login, and eventually gets a valid login.
 * 
 * In other words, this makes sure a locked out user stays locked out
 * even if they enter the correct password.
 * 
 * @param		object $user
 * @param		string $password
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_wp_authenticate_user($user, $password) {
	global $site_options;
	
	// Get the failures for the current user.
	$launchpad_login_failures = launchpad_get_failures($user->data->user_login);
	
	// Get the allowed faulures and set a default if none is set.
	$allowed_failures = $site_options['allowed_failures'];
	if(!$allowed_failures) {
		$allowed_failures = 10;
	}
	
	// Get the lockout time and set a default if none is set.
	$lockout_time = $site_options['lockout_time'];
	if(!$lockout_time) {
		$lockout_time = 1;
	}
	
	// Determine how many logins are left for the current user.
	$logins_left = ($allowed_failures-$launchpad_login_failures);
	
	// Normalize logins left to zero.
	if($logins_left < 1) {
		$logins_left = 0;
	}
	
	// If the user is an error or there are logins left, return the user.
	if(is_wp_error($user) || $logins_left > 0) {
		return $user;
	}
	
	// Otherwise, throw an error back to WordPress.
	$error = new WP_Error();
	$error->add('too_many_retries', 'Exhausted login attempts.');
	return $error;
}
if($GLOBALS['pagenow'] === 'wp-login.php') {
	add_filter('wp_authenticate_user', 'launchpad_wp_authenticate_user', 99999, 2);
}


/**
 * Add messaging to the login screen.
 *
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_login_add_error_message() {
	global $error, $site_options, $launchpad_login_failures, $launchpad_login_failures_cache;
	
	// If we don't have a any login failures, we don't need an error message.
	if(!$launchpad_login_failures) {
		return;
	}
	
	// Get the allowed faulures and set a default if none is set.
	$allowed_failures = $site_options['allowed_failures'];
	if(!$allowed_failures) {
		$allowed_failures = 10;
	}
	
	// Get the lockout time and set a default if none is set.
	$lockout_time = $site_options['lockout_time'];
	if(!$lockout_time) {
		$lockout_time = 1;
	}
	
	// Determine how many logins are left for the current user.
	$logins_left = ($allowed_failures-$launchpad_login_failures);
	
	// Normalize logins left to zero.
	if($logins_left < 1) {
		$logins_left = 0;
	}
	
	// If there are attempts left, send a message to the user.
	if($logins_left > 0) {
		$msg = '<br>You may try ' . $logins_left . ' more time' . ((int) $logins_left !== 1 ? 's' : '') . ' before triggering a ' . $lockout_time . ' hour' . ((int) $lockout_time !== 1 ? 's' : '') . ' lockout!';
		
		// If there are less than three left, suggest that the user reset his/her password.
		if($allowed_failures-$launchpad_login_failures < 3) {
			$msg .= '<br><strong>Please consider <a href="wp-login.php?action=lostpassword">resetting your password!</a></strong>';
		}
	
	// Otherwise, we tell the user they are SOL and for how long.
	} else {
		// Calculate how long, in minutes, until the user can try again.
		$lockout_elapsed = time() - filemtime($launchpad_login_failures_cache);
		$lockout_time_seconds = $lockout_time * 60 * 60;
		$lockout_time_left = ceil(($lockout_time_seconds-$lockout_elapsed)/60);
	
		$msg = '<br>You have been locked out.  You may try again in ' . $lockout_time_left . ' minute' . ((int) $lockout_time_left !== 1 ? 's' : '') . '.';
	}
	
	// If there is a message, add it to the global error that gets show to the user.
	if ($msg != '') {
		$error .= $msg;
	}
	return $error;
}
if($GLOBALS['pagenow'] === 'wp-login.php') {
	add_action('login_head', 'launchpad_login_add_error_message');
}


/**
 * Clear the Login Failures on Successful Login
 * 
 * @param		object $error_codes
 * @package 	Launchpad
 * @since		1.0
 */
function launchpad_clear_login_failures($user_login, $user) {
	$cache_file = launchpad_get_failures_cache($user_login);
	if(file_exists($cache_file)) {
		unlink($cache_file);
	}
}
if($GLOBALS['pagenow'] === 'wp-login.php' || is_admin()) {
	add_action('wp_login', 'launchpad_clear_login_failures', 10, 2);
}