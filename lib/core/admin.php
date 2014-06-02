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
 * Activate the Style Selector in MCE
 *
 * @since		1.0
 */
function launchpad_activate_style_select( $buttons ) {
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
add_filter('mce_buttons_2', 'launchpad_activate_style_select');


/**
 * Add Common Styles to MCE
 *
 * @since		1.0
 */
function launchpad_add_custom_mcs_styles( $init_array ) {  
	$launchpad_mce_style_formats = array(  
		array(  
			'title' => 'Button',
			'classes' => 'button',
			'wrapper' => false,
			'selector' => 'a'
		),
		array(  
			'title' => 'Crossfade Rotator',  
			'block' => 'div',  
			'classes' => 'skate',
			'wrapper' => true,
			'attributes' => (object) array('data-skate' => 'crossfade')
		)
	);
	
	$launchpad_mce_style_formats = apply_filters('launchpad_mce_style_formats', $launchpad_mce_style_formats);
	$launchpad_mce_style_formats = array_unique($launchpad_mce_style_formats);
	
	$init_array['style_formats'] = json_encode($launchpad_mce_style_formats);  
	return $init_array;  
  
} 
add_filter('tiny_mce_before_init', 'launchpad_add_custom_mcs_styles');  


/**
 * Add Custom Image Sizes to Admin Media Selector
 *
 * @since		1.0
 */
function launchpad_image_sizes_options($sizes) {
	global $_wp_additional_image_sizes;
	$tmp = array();
	foreach($_wp_additional_image_sizes as $image_name => $image_size) {
		$tmp[$image_name] = $image_name;
	}
    return array_merge($sizes, $tmp);
}
add_filter('image_size_names_choose', 'launchpad_image_sizes_options');


/**
 * Define basic theme settings fields
 *
 * @since		1.0
 */
function launchpad_get_setting_fields() {
	global $site_options;
	
	$lockouts = '';
	if(isset($site_options['lockout_time'])) {
		$lockout_time = $site_options['lockout_time'];
		if(!$lockout_time) {
			$lockout_time = 1;
		}
	} else {
		$lockout_time = 1;
	}
	
	$cache_dir = launchpad_get_cache_file();
	$cache_files = scandir($cache_dir);
	
	foreach($cache_files as $cache_file) {
		$cache_path = $cache_dir . $cache_file;
		$cache_file_split = explode('-', $cache_file);
		if($cache_file_split[0] === 'launchpad_limit_logins') {
			if(time()-filemtime($cache_path) <= $lockout_time*60*60) {
				$lockouts .= $cache_file_split[1] . ' @ ' . str_replace('.txt', '', $cache_file_split[2]) . '<br>';
			}
		}
	}
	
	if(!$lockouts) {
		$lockouts = 'No current lockouts.';
	}
	
	$opts = array(
			'security' => array(
				'name' => 'Security Settings <small class="launchpad-block">Save settings to clear all lockouts.<br><br><strong>Current Lockouts:</strong><br>' . $lockouts . '</small>',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(	
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
				)
			),
			'seo_social' => array(
				'name' => 'SEO and Social <small class="launchpad-block">Don\'t mess with this unless you know what you are doing.',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(
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
						)
					)
				),
			),
			'cache_options' => array(
				'name' => 'Caching <small class="launchpad-block">Save settings to clear all caches. Save page to clear related caches.</small>',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(	
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
				)
			),
			'offline_support' => array(
				'name' => 'Offline Support <small class="launchpad-block">All pages, 100 posts, and various assets will be available offline. <br><strong><em>NOT DEVELOPMENT FRIENDLY!</em></strong></small>',
				'args' => array(
					'small' => 'Enable offline support.',
					'type' => 'checkbox'
				)
			),
			'ajax_page_loads' => array(
				'name' => 'Ajax Page Loads',
				'args' => array(
					'small' => 'Attempt to load pages with ajax.',
					'type' => 'checkbox'
				)
			),
			'html5_bp' => array(
				'name' => 'HTML 5 Boilerplate',
				'args' => array(
					'small' => 'Include HTML5 Boilerplate in .htaccess.',
					'type' => 'checkbox'
				)
			),
			'organization_contact_info' => array(
				'name' => 'Organization Contact Info',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(
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
					)
				)
				
			),
			'organization_social' => array(
				'name' => 'Social Media <small class="launchpad-block">Use Full URLs to profile.</small>',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(					
						'organization_facebook' => array(
							'name' => 'Facebook',
							'args' => array(
								'type' => 'text'
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
					)
				)
			),
			'customizations' => array(
				'name' => 'Login Customizations',
				'args' => array(
					'type' => 'subfield',
					'subfields' => array(					
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
				)
			)
/*
			'sample_textarea' => array(
				'name' => 'Sample Textarea',
				'args' => array(
					'type' => 'textarea'
				)
			),
			'sample_selectmulti' => array(
				'name' => 'Sample Select Multi',
				'args' => array(
					'type' => 'selectmulti',
					'options' => array('key' => 'value')
				)
			)
*/
		);
		

	$opts = apply_filters('launchpad_setting_fields', $opts);
	
	// Add the ID as the name for each item
	foreach($opts as $k => $v) {
		$v['args']['name'] = $k;
		if($v['args']['type'] === 'subfield') {
			foreach($v['args']['subfields'] as $sf_key => $sf_val) {
				if(!isset($v['args']['label_for'])) {
					$v['args']['label_for'] = $sf_key;
				}
				$v['args']['subfields'][$sf_key]['args']['name'] = $sf_key;
			}
		} else {
			$v['args']['label_for'] = $k;
		}
		$opts[$k] = $v;
	}
	return $opts;
}



/**
 * Validate the inputs
 *
 * @param		array $input The array of options to validate
 * @since		1.0
 */
function launchpad_site_options_validate($input) {
	global $site_options;

	launchpad_clear_all_cache();
	$settings = launchpad_get_setting_fields();
	
	foreach($settings as $key => $setting) {
		if($setting['args']['type'] === 'checkbox') {
			if(!isset($input[$key]) || $input[$key] === '') {
				$input[$key] = false;
			} else {
				$input[$key] = true;
			}
		} else if($setting['args']['type'] === 'file') {

		}
		
		$site_options[$key] = $input[$key];
	}
	
	flush_rewrite_rules(true);
	
	$cache_folder = launchpad_get_cache_file();
	$all_files = scandir($cache_folder);
	foreach($all_files as $current_file) {
		if(preg_match('/^launchpad_limit_logins\-/', $current_file)) {
			unlink($cache_folder . $current_file);
		}
	}
	
	// Touch the API file to reset the appcache.
	// This helps avoid confusing issues with time zones.
	touch($cache_folder, time(), time());
	
	return $input;
}
 

/**
 * Register theme options page
 *
 * @since		1.0
 */
function launchpad_site_options_init() {
	register_setting('launchpad_options', 'launchpad_site_options', 'launchpad_site_options_validate');
	add_settings_section('launchpad_settings', 'General Options', '__return_false', 'launchpad_settings');
	
	$launchpad_options = launchpad_get_setting_fields();
	foreach($launchpad_options as $launchpad_option_id => $launchpad_option_details) {
		add_settings_field(
				$launchpad_option_id,
				$launchpad_option_details['name'],
				'launchpad_render_form_field',
				'launchpad_settings',
				'launchpad_settings',
				$launchpad_option_details['args']
			);
	}
}
add_action('admin_init', 'launchpad_site_options_init');
 

/**
 * Initialize theme options
 *
 * @since		1.0
 */
function launchpad_theme_options_add_page() {
	$opts = array(
			'parent_page' => false,
			'page_name' => 'Theme Options',
			'menu_name' => 'Special',
			'menu_icon' => 'dashicons-yes',
			'menu_position' => 999
		);
		
	$opts_orig = $opts;
	
	$opts = apply_filters('launchpad_theme_options_page', $opts);
	
	if($opts != $opts_orig) {
		if($opts['parent_page'] === false) {
			add_menu_page(
				$opts['page_name'],
				$opts['menu_name'],
				'edit_theme_options',
				'launchpad_settings',
				'launchpad_theme_options_render_page',
				$opts['menu_icon'],
				$opts['menu_position']
			);
		} else {
			add_submenu_page(
				$opts['parent_page'],
				$opts['page_name'],
				$opts['menu_name'],
				'edit_theme_options',
				'launchpad_settings',
				'launchpad_theme_options_render_page'
			);
		}
	} else {
		add_submenu_page(
			'options-general.php',
			'Launchpad Management',
			'Launchpad',
			'edit_theme_options',
			'launchpad_settings',
			'launchpad_theme_options_render_page'
		);
	}
}
add_action('admin_menu', 'launchpad_theme_options_add_page');
 

/**
 * Add theme options capability to theme
 *
 * @param		string $capability
 * @since		1.0
 */
function launchpad_option_page_capability($capability) {
	return 'edit_theme_options';
}
add_filter('option_page_capability_launchpad_options', 'launchpad_option_page_capability');
 

/**
 * Render the theme option page
 *
 * @since		1.0
 */
function launchpad_theme_options_render_page() {
	if(!current_user_can('manage_options')) {
		wp_die('You do not have sufficient permissions to access this page.');
	}
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php echo function_exists( 'wp_get_theme' ) ? wp_get_theme() : get_current_theme() ?> Settings</h2> 
		<form method="post" action="options.php">
			<?php
			
				settings_fields('launchpad_options');
				do_settings_sections('launchpad_settings');
				submit_button();
				
			?>
		</form>
	</div>
	<?php
}


/**
 * Remove access to certain pages for non-admins
 *
 * @since		1.0
 * @todo		Consider adding a filter here.
 */
function launchpad_remove_menu_pages() {
	$user = wp_get_current_user();
	if(!in_array('administrator', $user->roles)) {
		remove_menu_page('edit.php?post_type=acf');
	}
}
add_action('admin_menu', 'launchpad_remove_menu_pages');



/**
 * Provides a stylesheet and script hooks for the admin area
 *
 * @since		1.0
 */
function launchpad_admin_script_includes() {
	wp_register_style('launchpad_wp_admin_css', get_template_directory_uri() . '/css/admin-style.css', false, '1.0.0' );
	wp_enqueue_style('launchpad_wp_admin_css');
	wp_enqueue_script('launchpad_wp_admin_js', get_template_directory_uri() . '/js/admin.js');
}
add_action('admin_enqueue_scripts', 'launchpad_admin_script_includes');


/**
 * Remove unnecessary dashboard widgets
 * This is modified from the Roots theme.
 *
 * @since		1.0
 * @link http://www.deluxeblogtips.com/2011/01/remove-dashboard-widgets-in-wordpress.html
 */
function launchpad_remove_dashboard_widgets() {
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
	remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
	remove_meta_box('dashboard_primary', 'dashboard', 'normal');
	remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
}
add_action('admin_init', 'launchpad_remove_dashboard_widgets');


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
add_filter('gettext', 'launchpad_change_howdy', 10, 3);


/**
 * Customize the Login Screen
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
				$size = getimagesize($logo);

			
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
add_action('login_enqueue_scripts', 'launchpad_custom_login');


/**
 * Add Help Tab for Post Types
 *
 * Uses various "help" indexes on custom post types to create help tabs for documentation purposes.
 * 
 * @since		1.0
 */
function launchpad_auto_help_tab() {
	$post_types = launchpad_get_post_types();
	
	if(!$post_types) {
		return;
	}
	
	$screen = get_current_screen();
	
	if(isset($_GET['post_type'])) {
		$post_type = $_GET['post_type'];
	} else {
		$post_type = get_post_type( $post_ID );
	}
		
	if($post_types[$post_type]) {
		if($post_types[$post_type]['help']) {
			$screen->add_help_tab(
				array(
					'id' => $post_type . '-launchpad_help',
					'title' => $post_types[$post_type]['single'] . ' Overview',
					'content' => $post_types[$post_type]['help']
				)
			);
		}
		
		if($post_types[$post_type]['metaboxes']) {
			foreach($post_types[$post_type]['metaboxes'] as $metabox_key => $metabox) {
				$content = '';
				
				if($metabox['help']) {
					$content .= $metabox['help'];
				}
				
				$field_content = array();
				
				foreach($metabox['fields'] as $field) {
					if($field['help']) {
						$field_content[$field['name']] = $field['help'];
					}
					
					$generic_help = launchpad_get_field_help($field['args']['type']);
					
					if($generic_help) {
						$field_content[$field['name']] .= $generic_help;
					}
				}
				
				if($field_content) {
					$content .= '<p>The following fields are available:</p><dl>';
					foreach($field_content as $field_name => $field_help) {
						$content .= '<dt>' . $field_name . '</dt><dd>' . $field_help . '</dd>';
					}
					$content .= '</dl>';
				}
				
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
		
		if($post_types[$post_type]['flexible']) {
			foreach($post_types[$post_type]['flexible'] as $flex_key => $flex_details) {
				$content = '';
				
				if($flex_details['help']) {
					$content .= $flex_details['help'];
				}
				
				$module_content = array();
				
				foreach($flex_details['modules'] as $module) {
					$module_content[$module['name']] = array('help' => ($module['help'] ? $module['help'] : ''), 'fields' => array());
					
					foreach($module['fields'] as $field) {
						if($field['help']) {
							$module_content[$module['name']]['fields'][$field['name']] = $field['help'];
						}
						
						$generic_help = launchpad_get_field_help($field['args']['type']);
						
						if($generic_help) {
							$module_content[$module['name']]['fields'][$field['name']] .= $generic_help;
						}
					}
				}
				
				if($module_content) {
					$content .= '<dl>';
					foreach($module_content as $module_name => $module_help) {
						$content .= '<dt>' . $module_name . '</dt>';
						$content .= '<dd>';
						$content .= $module_help['help'];
						if($module_help['fields']) {
							$content .= '<p>The following fields are available:</p><dl>';
							foreach($module_help['fields'] as $field_name => $field_help) {
								$content .= '<dt>' . $field_name . '</dt><dd>' . $field_help . '</dd>';
							}
							$content .= '</dl>';
						}
						$content .= '</dd>';
					}
					$content .= '</dl>';
				}
				
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
add_action('admin_head', 'launchpad_auto_help_tab');



/**
 * Get Help Text for A Field Type
 *
 * @param		string $type The type of field to get help text for.
 * @since		1.0
 */
function launchpad_get_field_help($type) {
	$ret = '';
	switch($type) {
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