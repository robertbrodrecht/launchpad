<?php

/**
 * Admin Features
 *
 * Tweaks to admin-related WordPress features, including meta boxes and custom fields.
 *
 * @package 	Launchpad
 * @since		1.0
 */



/**
 * Simple Test for User Logged In
 *
 * @since	1.0
 */
function launchpad_user_logged_in() {
	header('Content-type: application/json');
	echo json_encode(is_user_logged_in());
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_user_logged_in', 'launchpad_user_logged_in');
	add_action('wp_ajax_nopriv_user_logged_in', 'launchpad_user_logged_in');
}


/**
 * Activate the Style Selector in MCE
 *
 * @since		1.0
 */
function launchpad_activate_style_select($buttons) {
	// Add support to MCE for style selection by inserting "styleselect" as 
	// the first item of the mce_buttons_2 array (under the "kitchen sink" button)
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
if(is_admin()) {
	add_filter('mce_buttons_2', 'launchpad_activate_style_select');
}


/**
 * Add Common Styles to MCE
 *
 * @since		1.0
 * @todo		Add back support for crossfade rotator once Skate is integrated.
 */
function launchpad_add_custom_mcs_styles($init_array) {
	// Create the inital array of styles supported by Launchpad.
	$launchpad_mce_style_formats = array(  
		array(
			'title' => 'Button',
			'classes' => 'button',
			'wrapper' => false,
			'selector' => 'a'
		),
		array(
			'title' => 'Callout',  
			'block' => 'div',  
			'classes' => 'callout',
			'wrapper' => true
		)
	);
	
	/*
	
	For future reference, this is to add Skate support:
	
	array(
		'title' => 'Crossfade Rotator',
		'block' => 'div',
		'classes' => 'skate',
		'wrapper' => true,
		'attributes' => (object) array('data-skate' => 'crossfade')
	)
	
	*/
	
	// Apply filters to allow the developer to change it.
	$launchpad_mce_style_formats = apply_filters('launchpad_mce_style_formats', $launchpad_mce_style_formats);
	
	// Apply the styles and return them.
	$init_array['style_formats'] = json_encode($launchpad_mce_style_formats);
	return $init_array;
}
if(is_admin()) {
	add_filter('tiny_mce_before_init', 'launchpad_add_custom_mcs_styles');
}


/**
 * Add Custom Image Sizes to Admin Media Selector
 *
 * @since		1.0
 */
function launchpad_image_sizes_options($sizes) {
	// Get the global listing of all registered image sizes.
	global $_wp_additional_image_sizes;
	
	// Create an empty array to add to.
	$tmp = array();
	
	// Add each size to the temporary array.
	foreach($_wp_additional_image_sizes as $image_name => $image_size) {
		$tmp[$image_name] = $image_name;
	}
	
	// Merge the existing sizes and the sizes set by the user.
    return array_merge($sizes, $tmp);
}
if(is_admin()) {
	add_filter('image_size_names_choose', 'launchpad_image_sizes_options');
}


/**
 * Define basic theme settings fields
 *
 * @since		1.0
 * @todo		Remove the textarea and selectmulti commented examples once they've been documented in 1.1.
 */
function launchpad_get_setting_fields() {
	global $site_options;
	
	// Set a default lockout time.
	$lockouts = '';
	if(isset($site_options['lockout_time'])) {
		$lockout_time = $site_options['lockout_time'];
		if(!$lockout_time) {
			$lockout_time = 1;
		}
	} else {
		$lockout_time = 1;
	}
	
	// Check for cache files.
	$cache_dir = launchpad_get_cache_file();
	
	if($cache_dir) {
		$cache_files = scandir($cache_dir);
	} else {
		$cache_files = array();
	}
	
	// Loop the cached files.
	foreach($cache_files as $cache_file) {
		$cache_path = $cache_dir . $cache_file;
		$cache_file_split = explode('-', $cache_file);
		
		// Create a list of lockouts based on file information.
		if($cache_file_split[0] === 'launchpad_limit_logins') {
			if(time()-filemtime($cache_path) <= $lockout_time*60*60) {
				$lockouts .= $cache_file_split[1] . ' @ ' . str_replace('.txt', '', $cache_file_split[2]) . '<br>';
			}
		}
	}
	
	// If there were no lockouts
	if(!$lockouts) {
		$lockouts = 'No current lockouts.';
	}
	
	// Default Launchpad site options.
	$opts = array(		
		'launchpad_options' => array(
			'parent_page' => 'options-general.php',
			'page_name' => 'Launchpad Management',
			'menu_name' => 'Launchpad',
			'menu_icon' => null,
			'menu_position' => null,
			'section_tabs' => true,
			'sections' => array(
				'organization_contact_info' => array(
					'section_name' => 'Contact Info',
					'description' => '',
					'fields' => array(
						'organization_name' => array(
							'name' => 'Organization Name',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_phone' => array(
							'name' => 'Phone Number',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_fax' => array(
							'name' => 'Fax Number',
							'args' => array(
								'type' => 'text'
							)
						),					
						'organization_address' => array(
							'name' => 'Street Address',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_city' => array(
							'name' => 'City',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_state' => array(
							'name' => 'State',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_zip' => array(
							'name' => 'Zip',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_email' => array(
							'name' => 'E-mail',
							'args' => array(
								'type' => 'text',
								'small' => 'Note: This will appear as the reply to on e-mails generated by WordPress!'
							)
						),	
					)
				),
				'organization_social' => array(
					'section_name' => 'Social Media',
					'description' => 'Use full URLs to profiles.',
					'fields' => array(
						'organization_facebook' => array(
							'name' => 'Facebook',
							'args' => array(
								'type' => 'text'
							)
						),
						'default_og_image' => array(
							'name' => 'Default OG Image',
							'args' => array(
								'type' => 'file'
							)	
						),
						'organization_twitter' => array(
							'name' => 'Twitter',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_linkedin' => array(
							'name' => 'LinkedIn',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_instagram' => array(
							'name' => 'Instagram',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_pinterest' => array(
							'name' => 'Pinterest',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_google' => array(
							'name' => 'Google+',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_youtube' => array(
							'name' => 'YouTube',
							'args' => array(
								'type' => 'text'
							)
						),
						'organization_flickr' => array(
							'name' => 'Flickr',
							'args' => array(
								'type' => 'text'
							)
						),
					)
				),
				'security' => array(
					'section_name' => 'Security',
					'description' => 'Save settings to clear all lockouts.<br><br><strong>Current Lockouts:</strong><br>' . $lockouts,
					'fields' => array(
						'allowed_failures' => array(
							'name' => 'Failures Before Lockout',
							'args' => array(
								'type' => 'select',
								'options' => array(
										'5' => '5',
										'10' => '10',
										'25' => '25',
										'50' => '50',
										'100' => '100'
									),
								'default' => '10'
							)
						),
						'lockout_time' => array(
							'name' => 'Lockout Time',
							'args' => array(
								'type' => 'select',
								'options' => array(
										'1' => '1',
										'2' => '2',
										'4' => '4',
										'6' => '6',
										'8' => '8',
										'12' => '12',
										'24' => '24',
										'48' => '48'
									),
								'default' => '1'
							)
						),
					)
				),
				'cache_options' => array(
					'section_name' => 'Caching',
					'description' => 'Save settings to clear all caches. Save page to clear related caches.<br>Your cache folder is ' . (is_writable($cache_dir) ? 'writable' : 'not writable') . ' with ' . (count($cache_files)-2) . ' files cached and located at: ' . $cache_dir,
					'fields' => array(
						'cache_timeout' => array(
							'name' => 'Cache Duration',
							'args' => array(
								'type' => 'select',
								'options' => array(
										'0' => 'Do Not Cache', 
										'300' => '5 Minutes',
										'600' => '10 Minutes',
										'900' => '15 Minutes',
										'1800' => '30 Minutes',
										'3600' => '1 Hour',
										'10800' => '3 Hours',
										'21600' => '6 Hours',
										'43200' => '12 Hours',
										'86400' => '1 Day',
									)
							)
						),
						'cache_debug_comments' => array(
							'name' => 'Show HTML comments with debug messages.',
							'args' => array(
								'small' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DO NOT USE IN PRODUCTION!',
								'type' => 'checkbox'
							)
						),
					)
				),
				'developer' => array(
					'section_name' => 'Developer',
					'description' => '',
					'fields' => array(
						'defer_js' => array(
							'name' => 'Defer JavaScript',
							'args' => array(
								'small' => 'Adds the defer attribute. If your plugins act up, try unchecking this.',
								'type' => 'checkbox',
								'default' => 'on'
							)
						),
						'html5_bp' => array(
							'name' => 'HTML 5 Boilerplate',
							'args' => array(
								'small' => 'Include HTML5 Boilerplate and Security Features in .htaccess.',
								'type' => 'checkbox'
							)
						),
						'google_analytics_id' => array(
							'name' => 'Google Analytics ID',
							'args' => array(
								'small' => 'A code like "UA-XXXXXX-X" provided in the <a href="http://google.com/analytics/" target="_blank">Google Analytics</a> Admin area.',
								'type' => 'text'
							)
						),
						'fb_app_id' => array(
							'name' => 'Facebook App ID',
							'args' => array(
								'type' => 'text'
							)
						),
						'fb_admin_id' => array(
							'name' => 'Facebook Admin IDs',
							'args' => array(
								'small' => 'Separate each ID by a comma without any spaces.',
								'type' => 'text'
							)
						),
						'twitter_card_username' => array(
							'name' => 'Twitter Card Username',
							'args' => array(
								'small' => 'DO NOT include the @.',
								'type' => 'text'
							)
						),
						'add_this_id' => array(
							'name' => 'Add This ID',
							'args' => array(
								'type' => 'text'
							)
						),
						'google_maps_api' => array(
							'name' => 'Google Maps API Key',
							'args' => array(
								'small' => 'Required to show maps.',
								'type' => 'text'
							)
						),
						'google_geocoding_api' => array(
							'name' => 'Google Geocoding API Key',
							'args' => array(
								'small' => 'Required to perform geocoding on address fields, but you may not need it.',
								'type' => 'text'
							)
						),
						'primary_color' => array(
							'name' => 'Primary Hex Color',
							'args' => array(
								'type' => 'text'
							)
						),
						'secondary_color' => array(
							'name' => 'Secondary Hex Color',
							'args' => array(
								'type' => 'text'
							)
						),
						'logo' => array(
							'name' => 'Logo Upload',
							'args' => array(
								'type' => 'file'
							)
						)
					)
				),
			)
		)
	);
		

	// Apply filters so the developer can change it.
	$opts = apply_filters('launchpad_setting_fields', $opts);
	
	// Add the ID as the name for each item.
	// This is to save the developer some typing and duplication of key/values.
	foreach($opts as $page_id => $page_details) {
		foreach($page_details['sections'] as $section_id => $section_details) {
			foreach($section_details['fields'] as $field_id => $field_details) {
				$field_details['args']['name'] = $field_id;
				if($field_details['args']['type'] === 'subfield') {
					foreach($field_details['args']['subfields'] as $sf_key => $sf_val) {
						if(!isset($field_details['args']['label_for'])) {
							$field_details['args']['label_for'] = $sf_key;
						}
						$field_details['args']['subfields'][$sf_key]['args']['name'] = $sf_key;
					}
				} else {
					$field_details['args']['label_for'] = $field_id;
				}
				$opts[$page_id]['sections'][$section_id]['fields'][$field_id] = $field_details;
			}
		}
	}
	return $opts;
}



/**
 * Validate the inputs
 *
 * @param		array $input The array of options to validate
 * @since		1.0
 */
function launchpad_site_options_validate($input = true) {
	global $site_options;
	
	// Clear all cached files when the settings are saved.
	launchpad_clear_all_cache();
	
	// Flush rewrite rules when settings are saved.
	flush_rewrite_rules(true);
	
	// Clear all lockouts when settings are saved.
	$cache_folder = launchpad_get_cache_file();
	if(!file_exists($cache_folder)) {
		@mkdir($cache_folder, 0777);
	}
	if(!file_exists($cache_folder)) {
		$all_files = array();
	} else {
		$all_files = scandir($cache_folder);
	}
	foreach($all_files as $current_file) {
		if(preg_match('/^launchpad_limit_logins\-/', $current_file)) {
			unlink($cache_folder . $current_file);
		}
	}
	
	// Touch the cache folder to reset the appcache.
	// Basically, causes appcache "resets" when you save the site settings
	// because the "latest" time is set to the lastmod of the cache folder.
	// Touching it helps avoid confusing issues with time zones (that's what she said).
	touch($cache_folder, time(), time());
	
	if((int) $site_options['cache_timeout'] > 0) {
		$db_conts = file_get_contents(dirname(__FILE__) . '/db.php');
	} else {
		$db_conts = '';
	}
	
	if(
		(file_exists(WP_CONTENT_DIR . '/db.php') && is_writable(WP_CONTENT_DIR . '/db.php')) || 
		(!file_exists(WP_CONTENT_DIR . '/db.php') && is_writable(WP_CONTENT_DIR))
	) {
		$f = fopen(WP_CONTENT_DIR . '/db.php', 'w');
		fwrite($f, $db_conts);
		fclose($f);
	}
	
	do_action('launchpad_site_options_validate');
	return $input;
}
 

/**
 * Register theme options page
 *
 * @since		1.0
 */
function launchpad_site_options_init() {
	// Add support for an options page.
	//register_setting('launchpad_options', 'launchpad_site_options', 'launchpad_site_options_validate');
	//add_settings_section('launchpad_settings', 'General Options', '__return_false', 'launchpad_settings');
	
	if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
		launchpad_site_options_validate();
	}
	
	// Get the settings fields.
	$launchpad_options = launchpad_get_setting_fields();
	
	
	// Loop pages and add menus
	foreach($launchpad_options as $page_id => $page_details) {
		if($page_details['parent_page']) {
			add_submenu_page(
				$page_details['parent_page'],
				$page_details['page_name'],
				$page_details['menu_name'],
				'edit_theme_options',
				$page_id,
				'launchpad_theme_options_render_page'
			);
		} else {
			add_menu_page(
				$page_details['page_name'],
				$page_details['menu_name'],
				'edit_theme_options',
				$page_id,
				'launchpad_theme_options_render_page',
				$page_details['menu_icon'],
				$page_details['menu_position']
			);
		}
		
		// Loop sections and add section support.
		foreach($page_details['sections'] as $section_id => $section_details) {
			if($page_details['section_tabs']) {
				add_settings_section(
					$section_id, 
					$section_details['section_name'], 
					'launchpad_render_form_section', 
					$section_id
				);
			} else {
				add_settings_section(
					$section_id, 
					$section_details['section_name'], 
					'launchpad_render_form_section', 
					$page_id
				);
			}
			
			// Loop fields and register the fiels.
			foreach($section_details['fields'] as $launchpad_option_id => $launchpad_option_details) {
				if($page_details['section_tabs']) {
					register_setting($section_id, $launchpad_option_id);
					add_settings_field(
						$launchpad_option_id,
						$launchpad_option_details['name'],
						'launchpad_render_form_field',
						$section_id,
						$section_id,
						$launchpad_option_details['args']
					);					
				} else {
					register_setting($page_id, $launchpad_option_id);
					add_settings_field(
						$launchpad_option_id,
						$launchpad_option_details['name'],
						'launchpad_render_form_field',
						$page_id,
						$section_id,
						$launchpad_option_details['args']
					);
				}
			}
		}
	}
}
if(is_admin()) {
	add_action('admin_menu', 'launchpad_site_options_init');
}


/**
 * Renders the Form Section
 *
 * @since		1.0
 */
function launchpad_render_form_section($section) {
	global $wp_settings_fields;
	
	$settings = launchpad_get_setting_fields();
	$page_settings = $settings[$_GET['page']];
	$section_settings = $page_settings['sections'][$section['id']];
	
	if(isset($section_settings['description']) && $section_settings['description']) {
		echo wpautop('<small>' . $section_settings['description'] . '</small>');
	}
}
 

/**
 * Render the theme option page
 *
 * @since		1.0
 */
function launchpad_theme_options_render_page() {
	global $site_options;
	
	// If the current user lacks privileges, block them.
	if(!current_user_can('manage_options')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	
	$settings = launchpad_get_setting_fields();
	$settings = $settings[$_GET['page']];
	
	if(is_multisite()) {
		?>
		<div class="updated">
			<p><strong>If you've recently changed themes on this site, you MUST update the .htaccess file to use pretty URLs for images, JavaScript, and CSS.  This is done by going to the Network Admin, then going to Settings &gt; Network Setup.  If you do not have access to this area, please contact your network administrator.</strong></p>
		</div>
		<?php
	}
	
	// Otherwise, render the options page wrapper.
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo $settings['page_name'] ?></h2>
		<?php
		
		if(
			!is_writable(WP_CONTENT_DIR) && 
			(
				!file_exists(WP_CONTENT_DIR . '/db.php') || 
				!is_writable(WP_CONTENT_DIR . '/db.php')
			)
		) {
			if(@$site_options['cache_timeout'] > 0) {
				echo '<div class="error"><p><strong>Database Caching is disabled!</strong>  To enable database caching, create an empty file at wp-content/db.php, make the file writable, and save settings.</p></div>';
			}
		}
		
		if(
			!is_writable($_SERVER['DOCUMENT_ROOT']) && 
			(
				!file_exists($_SERVER['DOCUMENT_ROOT'] . '/.htaccess') || 
				!is_writable($_SERVER['DOCUMENT_ROOT'] . '/.htaccess')
			)
		) {
			if((int) $site_options['html5_bp'] > 0) {
				echo '<div class="error"><p><strong>HTML5 Boilerplate Not Installed!</strong>  To enable installation of HTML Boilerplate, make the .htaccess file writable.</p></div>';
			}
		}
		
		$wp_config = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
		
		if(stristr($wp_config, 'put your unique phrase here') !== false) {
			echo '<div class="error"><p><strong>Salt Your Keys!</strong>  Open wp-config.php and salt your keys.  <strong>DO IT RIGHT NOW!!!</strong></p></div>';
		}
		
		if(stristr($wp_config, '$table_prefix  = \'wp_\';') !== false) {
			echo '<div class="error"><p><strong>Change Your Table Prefix!</strong>  Open wp-config.php and change your table prefix from "wp_" to anything else.  Unfortunately, since you didn\'t do this when you were setting up WordPress, you will probably have to reinstall WordPress to make this warning go away.</p></div>';
		}
		
		settings_errors();
		
		if(!isset($_GET['tab'])) {
			reset($settings['sections']);
			$_GET['tab'] = key($settings['sections']);
		}
		
		if($settings['section_tabs']) {
			echo '<h2 class="nav-tab-wrapper">';
			foreach($settings['sections'] as $section_id => $section_details) {
				?>
				<a href="?page=<?= $_GET['page'] ?>&tab=<?= $section_id ?>" class="nav-tab<?= $_GET['tab'] === $section_id ? ' nav-tab-active' : '' ?>"><?= $section_details['section_name'] ?></a>
				<?php
			}
			echo '</h2>';
		}
		
		?> 
		<form method="post" action="options.php">
			<?php
				
				if($settings['section_tabs']) {
					foreach($settings['sections'] as $section_id => $section_details) {
						if($_GET['tab'] === $section_id) {
							settings_fields($section_id);
							do_settings_sections($section_id);
						}
					}
				} else {
					settings_fields($_GET['page']);
					do_settings_sections($_GET['page']);
				}
				
				submit_button();
				
			?>
		</form>
	</div>
	<?php
}


/**
 * Add notices for critical things.
 *
 * @since		1.0
 */
function launchpad_admin_notices() {
	if(!get_option('blog_public')) {
		echo '<div class="error"><p><strong>Search Engines Blocked!</strong>  Don\'t forget that search engines are still being blocked.  To change that, go to Settings > Reading and update uncheck "Discourage search engines from indexing this site."</p></div>';
	}
}
if(is_admin()) {
	add_action('admin_notices', 'launchpad_admin_notices');
}


/**
 * Provides a stylesheet and script hooks for the admin area
 *
 * @since		1.0
 */
function launchpad_admin_script_includes() {
	// Add admin-style.css.
	wp_register_style('launchpad_wp_admin_css', get_template_directory_uri() . '/css/admin-style.css', false, '1.0.0' );
	wp_enqueue_style('launchpad_wp_admin_css');
	
	// Add admin.js except on user pages because something is breaking the loader in 4.3.
	//if(stristr($GLOBALS['pagenow'], 'user') === false && stristr($GLOBALS['pagenow'], 'profile') === false) {
		wp_enqueue_script('launchpad_wp_admin_js', get_template_directory_uri() . '/js/admin-min.js');
	//}
}
if(is_admin()) {
	add_action('admin_enqueue_scripts', 'launchpad_admin_script_includes');
}


/**
 * Remove unnecessary dashboard widgets
 * 
 * This is modified from the Roots theme.
 *
 * @since		1.0
 * @link		http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
 */
function launchpad_remove_dashboard_widgets() {
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	remove_meta_box('dashboard_primary', 'dashboard', 'normal');
	remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
}
if(is_admin()) {
	add_action('admin_init', 'launchpad_remove_dashboard_widgets');
}


/**
 * Changes "Howdy" to "Hello" in the admin bar
 *
 * @param		string $translated The original text
 * @param		string $text
 * @param		string $domain
 * @since		1.0
 */
function launchpad_change_howdy($translated, $text, $domain) {
	if (false !== strpos($translated, 'Howdy')) {
		return str_replace('Howdy', 'Hello', $translated);
	}
	return $translated;
}
if(is_admin()) {
	add_filter('gettext', 'launchpad_change_howdy', 10, 3);
}


/**
 * Customize the Login Screen
 * 
 * Most of the customizations are handled in admin-style.scss, but these particular ones 
 * require PHP to render user-defined values.
 * 
 * @since		1.0
 */
function launchpad_custom_login() {
	global $site_options;
	
?>
	<meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
	<link rel="stylesheet" href="/css/admin-style.css">
	<style type="text/css">
		html,
		body.login {
			overflow: auto;
		}
		<?php 
		
		if($site_options['logo']) { 
			$logo = wp_get_attachment_image_src($site_options['logo'], 'full');
			
			if($logo) {
				$logo = $logo[0];
				$size = getimagesize($_SERVER['DOCUMENT_ROOT'] . $logo);
				
		?>
		body.login div#login h1 a {
			background-image: url(<?php echo $logo ?>);
			background-size: contain;
			height: <?php echo ((!$size || $size[0] > $size[1]) ? '80px' : '400px'); ?>;
			width: 100%;
		}
		<?php
		 
			}
		} 
		
		?>
		
		<?php if($site_options['primary_color']) { ?>
		body.login #nav a:hover, 
		body.login #backtoblog a:hover,
		body.login #nav a:focus,
		body.login #backtoblog a:focus,
		a:active {
			color: <?php echo $site_options['primary_color'] ?>;
		}
		
		.wp-core-ui .button-primary,
		.wp-core-ui .button-primary:hover,
		.wp-core-ui .button-primary:focus,
		.wp-core-ui .button-primary:active {
			background: <?php echo $site_options['primary_color'] ?>;
			border: 1px solid rgba(0, 0, 0, .1);
			box-shadow: none;
		}
		<?php } ?>
		<?php if($site_options['secondary_color']) { ?>
		body.login form {
			border: 1px solid <?php echo $site_options['secondary_color']; ?>
		}
		<?php } ?>
	</style>
	
<?php 

}
if($GLOBALS['pagenow'] === 'wp-login.php') {
	add_action('login_enqueue_scripts', 'launchpad_custom_login');
}


/**
 * Add Help Tab for Post Types
 *
 * Uses various "help" indexes on custom post types to create help tabs for documentation purposes.
 * 
 * @since		1.0
 * @todo		In 1.1 documentation, don't forget to include the "NOTE:" stuff below.
 */
function launchpad_auto_help_tab() {
	// Get the developer created post types.
	$post_types = launchpad_get_post_types();
	
	// If there are no post types, don't do anything.
	if(!$post_types) {
		return;
	}
	
	// Get the current screen to add help to it.
	$screen = get_current_screen();
	
	// Set the post type or get it from the post.
	if(isset($_GET['post_type'])) {
		$post_type = $_GET['post_type'];
	} else {
		$post_type = get_post_type();
	}
	
	// If there are custom fields associated with the post type, start checking them for help.	
	if(isset($post_types[$post_type])) {
		
		// If there is a help section, add a help tab.
		// NOTE: This means you can have help in your fields WITHOUT a help tab showing up.
		// NOTE: You should document your post type! At least give an overview of what it is.
		if(isset($post_types[$post_type]['help'])) {
			$screen->add_help_tab(
				array(
					'id' => $post_type . '-launchpad_help',
					'title' => $post_types[$post_type]['single'],
					'content' => $post_types[$post_type]['help']
				)
			);
		}
		
		// Generate help data for each metabox type if metaboxes exist.
		if(isset($post_types[$post_type]['metaboxes'])) {
			
			// Loop the metaboxes.
			foreach($post_types[$post_type]['metaboxes'] as $metabox_key => $metabox) {
				
				// This string will carry the help text.
				$content = '';
				
				// If the metbox itself has help, add it to the text.
				if(isset($metabox['help'])) {
					$content .= $metabox['help'];
				}
				
				// This is used to hold each field's help text.
				$field_content = array();
				
				// Loop the fields.
				foreach($metabox['fields'] as $field) {
					
					// If the field has help, add it to the string.
					// We're using the name to help with with the output loop (key = name, value = help text).
					if(isset($field['help'])) {
						$field_content[$field['name']] = $field['help'];
					}
					
					// Try to get generic help about the field type.
					// Some of the more complex fields need some generic documentation.
					$generic_help = launchpad_get_field_help($field['args']['type']);
					
					// If there is generic help, add it to the field's help text.
					if($generic_help) {
						if(!isset($field_content[$field['name']])) {
							$field_content[$field['name']] = '';
						}
						$field_content[$field['name']] .= $generic_help;
					}
					
					if($field['args']['type'] === 'repeater') {
						if(!isset($field_content[$field['name']])) {
							$field_content[$field['name']] = '';
						}
						$field_content[$field['name']] .= '<p><strong>This field offers additional fields:</strong></p>';
						foreach($field['args']['subfields'] as $single_field) {
							if(isset($single_field['help']) && $single_field['help']) {
								$field_content[$field['name']]  .= '<p><strong>' . $single_field['name'] . '</strong></p>' . $single_field['help'];
							}
						}
					}
				}
				
				// If we have created any field-based help, add it to the help text in a definition list.
				if($field_content) {
					$content .= '<p>The following fields are available:</p><dl>';
					
					// Loop with key = name, value = help text, as mentioned earlier.
					foreach($field_content as $field_name => $field_help) {
						$content .= '<dt>' . $field_name . '</dt><dd>' . $field_help . '</dd>';
					}
					$content .= '</dl>';
				}
				
				// If the help text isn't empty, add a help tab.
				if($content) {
					$screen->add_help_tab(
						array(
							'id' => $post_type . '-' . $metabox_key . '-launchpad_help',
							'title' => $metabox['name'] . ' Overview',
							'content' => '<div class="launchpad-help-container">' . $content . '</div>'
						)
					);
				}
			}
		}
		
		// Generate help data for each flexible content, if any exist.
		if(isset($post_types[$post_type]['flexible'])) {
			// Loop the flexible content types.
			foreach($post_types[$post_type]['flexible'] as $flex_key => $flex_details) {
				
				// This string will carry the help text.
				$content = '';
				
				// If the flexible content itself has help, add it to the text.
				if(isset($flex_details['help'])) {
					$content .= $flex_details['help'];
				}
				
				// This is used to hold each flexible content module's help text.
				$module_content = array();
				
				// Loop each module to look for help text.
				foreach($flex_details['modules'] as $module) {
					
					// If any exists, create a module-name key with the module help 
					// text and an empty array for individual field help.
					// The name / value stuff, again, is used for easier output in a loop.
					$module_content[$module['name']] = array('help' => (isset($module['help']) ? $module['help'] : ''), 'fields' => array());
					
					// Loop the fields.
					if(isset($module['fields'])) {
						foreach($module['fields'] as $field) {
							
							// If the fiel has help, add it to the module's help text temporary holding place.
							if(isset($field['help'])) {
								$module_content[$module['name']]['fields'][$field['name']] = $field['help'];
							}
							
							if($field['args']['type'] === 'repeater') {
								$module_content[$module['name']]['fields'][$field['name']] .= '<p><strong>This field offers additional fields:</strong></p>';
								foreach($field['args']['subfields'] as $single_field) {
									if(isset($single_field['help']) && $single_field['help']) {
										$module_content[$module['name']]['fields'][$field['name']]  .= '<p><strong>' . $single_field['name'] . '</strong></p>' . $single_field['help'] . '</p>';
									}
								}
							}
														
							// Try to get generic help about the field type.
							// Some of the more complex fields need some generic documentation.
							$generic_help = launchpad_get_field_help($field['args']['type']);
							
							// If there is generic help, add it to the field's help.						
							if($generic_help) {
								$module_content[$module['name']]['fields'][$field['name']] .= $generic_help;
							}
						}
					}
				}
				
				// If there is module help content, add it to the module help content.
				if($module_content) {
					$content .= '<dl>';
					
					// Loop the main module, adding help.  See the key / value stuff I said above.
					foreach($module_content as $module_name => $module_help) {
						$content .= '<dt>' . $module_name . '</dt>';
						$content .= '<dd>';
						$content .= $module_help['help'];
						
						// If there is any help in the fields...
						if(isset($module_help['fields'])) {
							$content .= '<p>The following fields are available:</p><dl>';
							
							// Loop the fields and add each field's help.
							foreach($module_help['fields'] as $field_name => $field_help) {
								$content .= '<dt>' . $field_name . '</dt><dd>' . $field_help . '</dd>';
							}
							$content .= '</dl>';
						}
						$content .= '</dd>';
					}
					$content .= '</dl>';
				}
				
				// If any help text was created, add a help tab.
				if($content) {
					$screen->add_help_tab(
						array(
							'id' => $post_type . '-' . $flex_key . '-launchpad_help',
							'title' => $flex_details['name'] . ' Overview',
							'content' => '<div class="launchpad-help-container">' . $content . '</div>'
						)
					);
				}
			}
		}
	}
}
if(is_admin()) {
	add_action('admin_head', 'launchpad_auto_help_tab');
}


/**
 * Get Help Text for A Field Type
 * 
 * As you can see, we check the field type with a switch and return a pre-written
 * string with help documentation that is used to show in help areas.
 *
 * @param		string $type The type of field to get help text for.
 * @since		1.0
 * @see			launchpad_auto_help_tab()
 */
function launchpad_get_field_help($type) {
	$ret = '';
	switch($type) {
		case 'address':
			$ret = '<p>The address field type is used to enter complex addresses.  Enter the address to the best of your ability.  When you save the post, if you have entered a Google Maps API Key in Launchpad Settings, the address will automatically be geocoded if possible.</p>';
		break;
		case 'file':
			$ret = '<p>To use this field, click the "Upload File" button, then either browse through your Media Library or drag-and-drop a new file to upload.  Once you have found the file, make sure it is selected with a check mark at the top right of the file icon, then click the "Add File" button.</p>';
		break;
		case 'selectmulti':
			$ret = '<p>This field allows you to select one or more items.  On a Mac, use the command key to select multiple items.  On a PC, use the control key to select multiple items.</p>';
		break;
		case 'repeater':
			$ret = '<p>This field allows you to add multiple repeating items.  Click the button below the first item that reads something similar to "Add Additional Item" to add an additional set of fields.</p>';
		break;
		case 'relationship':
			$ret = '<p>This field allows you to attach one or more posts as a list (limitations of this specific field are listed in parenthesis on the right panel next to the text "Saved Items").</p><p>If you don\'t see the post you need, type in the search box to help narrow down the results.  Once you have found the post you need, click it to add it to the "Saved Items" list.</p><p>You can sort the saved items with drag-and-drop.  To remove a saved item, click it in the list of saved items.</p>';
		break;
		case 'taxonomy':
			$ret = '<p>This field allows you to select one or more taxonomy by checking the appropriate checkbox.</p>';
		break;
	}
	return $ret;
}


/**
 * Add Responsive Image Fields
 * 
 * @since		1.3
 */
function launchpad_add_media_responsive_images($post_types) {
	if(!isset($post_types['attachment'])) {
		$post_types['attachment'] = array();
	}
	if(!isset($post_types['attachment'])) {
		$post_types['attachment']['metaboxes'] = array();
	}
	
	global $_wp_additional_image_sizes;
	
	$all_size_classes = get_intermediate_image_sizes();
	$size_classes = array();
	foreach($_wp_additional_image_sizes as $size_key => $size_val) {
		$size_classes[$size_key] = $size_key . ' (' . $size_val['width'] . '×' . $size_val['height'] . ', ' . ($size_val['crop'] ? 'Cropped' : 'Uncropped') . ')';
	}
	
	$post_types['attachment']['metaboxes']['launchpad_responsive_images'] = array(
		'name' => 'Responsive Image Settings',
		'help' => '<p>Use the following to set up responsive images that will be attached to this image. Do note that there is no way to do a one-size-fits-all solution.  We\'ll just try to do the best we can based on the sites we make.  It is best to use the MOBILE 1X image as the base image and add larger sizes to it.  This will allow browsers that use a polyfill to download the smallest possible version of the image first instead of the largest before the polyfill runs.</p>',
		'location' => 'normal',
		'position' => 'default',
		'fields' => array(
			'mobile_width' => array(
				'name' => 'Image Width at Mobile',
				'args' => array(
					'type' => 'text',
					'default' => '100vw'
				)
			),
			'responsive_sizes' => array(
				'name' => 'Sizes',
				'help' => '<p>The sizes the image is at different media query widths.</p>',
				'args' => array(
					'type' => 'repeater',
					'label' => 'Size',
					'subfields' => array(
						'media' => array(
							'name' => 'Media Query',
							'args' => array(
								'type' => 'text',
								'small' => 'For example: (min-width: 300px)'
							)
						),
						'width' => array(
							'name' => 'Image Width at Media Query',
							'args' => array(
								'type' => 'text',
								'small' => 'For example: 300px'
							)
						)
					)
				)
			),
			'responsive_images' => array(
				'name' => 'Images',
				'help' => '<p>Attach the matching file or specify a WordPress size class.  If you upload an image AND select a size class, the specific size class of that image will be used.  If you only specify an image, the full sized image will be used.  If you only specify a size class, the size class for the current image will be used.</p>',
				'args' => array(
					'type' => 'repeater',
					'label' => 'Size',
					'subfields' => array(
						'width' => array(
							'name' => 'Image',
							'args' => array(
								'type' => 'file'
							)
						),
						'size' => array(
							'name' => 'Size Class',
							'args' => array(
								'type' => 'select',
								'options' => $size_classes
							)
						)
					)
				)
			)
		)
	);
	return $post_types;
}
add_filter('launchpad_custom_post_types', 'launchpad_add_media_responsive_images', 2);



/*
function launchpad_image_srcset($html = false, $id = false, $caption = false, $title = false, $align = false, $url = false, $size = false, $alt = false) {
	
	$res = array($html, $id, $caption, $title, $align, $url, $size, $alt);
	$eml = '';
	foreach($res as $r) {
		$eml .= var_export($r, true);
	}
	
	wp_mail('robert@bigcom.com', 'WP Test', $eml);
	
	
	return $html;
}
add_filter('image_send_to_editor', 'launchpad_image_srcset');
*/
