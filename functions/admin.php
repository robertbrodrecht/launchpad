<?php

/**
 * Admin Features
 *
 * Tweaks to admin-related WordPress features, including meta boxes and custom fields.
 *
 * @package 	Launchpad
 * @since   	Version 1.0
 */



/**
 * Activate the Style Selector
 *
 * @since   	Version 1.0
 */
function launchpad_activate_style_select( $buttons ) {
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
add_filter('mce_buttons_2', 'launchpad_activate_style_select');


/**
 * Add Common Styles.
 *
 * @since   	Version 1.0
 */
function launchpad_add_custom_mcs_styles( $init_array ) {  
	global $launchpad_mce_style_formats;
	$init_array['style_formats'] = json_encode($launchpad_mce_style_formats);  
	return $init_array;  
  
} 
add_filter('tiny_mce_before_init', 'launchpad_add_custom_mcs_styles');  


/**
 * Add Custom Image Sizes to Admin Selector
 *
 * @since   	Version 1.0
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
 * @since   	Version 1.0
 */
function launchpad_get_setting_fields() {
	
	$opts = array(
			'google_analytics_id' => array(
				'name' => 'Google Analytics ID',
				'args' => array(
					'small' => 'A code like "UA-XXXXXX-X" provided in the <a href="http://google.com/analytics/" target="_blank">Google Analytics</a> Admin area.',
					'type' => 'text'
				)
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
 * Generate OPTIONs based on an array
 *
 * @param		array $options The array of options to build
 * @param		array $values The array of values to pick selected options
 * @since   	Version 1.0
 */
function launchpad_create_select_options($options, $values) {
	$ret = '';
	foreach($options as $option_value => $option_text) {
		if(is_array($option_text)) {
			$ret .= '<optgroup label="' . ucwords($option_value) . '">';
			foreach($option_text as $sub_option_value => $sub_option_text) {
				$ret .= '<option value="' . $sub_option_value . '"' . (is_array($values) ? (in_array($sub_option_value, $values) ? ' selected="selected"' : '') : ($values == $sub_option_value ? ' selected="selected"' : '')) . '>' . $sub_option_text . '</option>';
			}
			$ret .= '</optgroup>';
		} else {
			$ret .= '<option value="' . $option_value . '"' . (is_array($values) ? (in_array($option_value, $values) ? ' selected="selected"' : '') : ($values == $option_value ? ' selected="selected"' : '')) . '>' . $option_text . '</option>';
		}
	}
	return $ret;
}

/**
 * Render fields
 *
 * @param		array $args The array of settings
 * @see			launchpad_get_setting_fields
 * @since   	Version 1.0
 */
function launchpad_render_settings_field($args, $subfield = false) {
	$vals = get_option('launchpad_site_options', '');
	if(isset($vals[$args['name']]))  {
		$val = $vals[$args['name']];
	} else {
		$val = '';
	}
	
	if($subfield) {
		$class = 'launchpad-subfield ' . sanitize_title($subfield);
	}
	
	switch($args['type']) {
		case 'checkbox':
			if($subfield) {
				echo '<label class="' . $class . '">';
			}
			echo '<input type="checkbox" name="launchpad_site_options[' . $args['name'] . ']" id="' . $args['name'] . '" ' . ($val ? ' checked="checked"' : '') . '>';
			if($subfield) {
				echo ' ' . $subfield . '</label>';
			}
		break;
		case 'text':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			echo '<input type="text" name="launchpad_site_options[' . $args['name'] . ']" id="' . $args['name'] . '" value="' . $val . '" class="regular-text">';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'textarea':
			echo '<textarea name="launchpad_site_options[' . $args['name'] . ']" id="' . $args['name'] . '" rows="10" cols="50" class="large-text code">' . $val . '</textarea>';
		break;
		case 'select':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			echo '<select name="launchpad_site_options[' . $args['name'] . ']" id="' . $args['name'] . '">';
			echo '<option value="">Select One</option>';
			echo launchpad_create_select_options($args['options'], $val);
			echo '</select>';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'selectmulti':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			echo '<select name="launchpad_site_options[' . $args['name'] . '][]" size="10" multiple="multiple" id="' . $args['name'] . '">';
			echo launchpad_create_select_options($args['options'], $val);
			echo '</select>';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'subfield':
			foreach($args['subfields'] as $field) {
				launchpad_render_settings_field($field['args'], $field['name']);
			}
		break;
	}
	if(isset($args['small'])) {
		if($args['type'] !== 'checkbox' || $subfield !== false) {
			$class = 'launchpad-block';
		} else {
			$class = 'launchpad-inline';
		}
		echo '<small class="' . $class . '">' . $args['small'] . '</small>';
	}
}
 

/**
 * Validate the inputs
 *
 * @param		array $input The array of options to validate
 * @since   	Version 1.0
 */
function launchpad_site_options_validate($input) {
	global $site_options;

	launchpad_clear_all_cache();
	$settings = launchpad_get_setting_fields();
	
	foreach($settings as $key => $setting) {
		if($setting['args']['type'] === 'checkbox') {
			if(!isset($input[$key])) {
				$input[$key] = false;
			} else {
				$input[$key] = true;
			}
		}
		
		$site_options[$key] = $input[$key];
	}
	
	flush_rewrite_rules(true);
	
	return $input;
}
 

/**
 * Register theme options page
 *
 * @since   	Version 1.0
 */
function launchpad_site_options_init() {
	register_setting('launchpad_options', 'launchpad_site_options', 'launchpad_site_options_validate');
	add_settings_section('launchpad_settings', 'General Options', '__return_false', 'launchpad_settings');
	
	$launchpad_options = launchpad_get_setting_fields();
	foreach($launchpad_options as $launchpad_option_id => $launchpad_option_details) {
		add_settings_field(
				$launchpad_option_id,
				$launchpad_option_details['name'],
				'launchpad_render_settings_field',
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
 * @since   	Version 1.0
 */
function launchpad_theme_options_add_page() {
	$theme_page = add_theme_page(
		'Launchpad Settings',
		'Theme Settings',
		'edit_theme_options',
		'launchpad_settings',
		'launchpad_theme_options_render_page'
	);
}
add_action('admin_menu', 'launchpad_theme_options_add_page');
 

/**
 * Add theme options capability to theme
 *
 * @param		string $capability
 * @since   	Version 1.0
 */
function launchpad_option_page_capability($capability) {
	return 'edit_theme_options';
}
add_filter('option_page_capability_launchpad_options', 'launchpad_option_page_capability');
 

/**
 * Render the theme option page
 *
 * @since   	Version 1.0
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
 * @since   	Version 1.0
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
 * @since   	Version 1.0
 */
function launchpad_admin_script_includes() {
	wp_register_style('launchpad_wp_admin_css', get_template_directory_uri() . '/css/admin-style.css', false, '1.0.0' );
	wp_enqueue_style('launchpad_wp_admin_css');
	wp_enqueue_script('my_custom_script', get_template_directory_uri() . '/js/admin.js');
}
add_action('admin_enqueue_scripts', 'launchpad_admin_script_includes');


/**
 * Remove unnecessary dashboard widgets
 * This is modified from the Roots theme.
 *
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
 * @since   	Version 1.0
 */
function launchpad_change_howdy($translated, $text, $domain) {
	if (false !== strpos($translated, 'Howdy')) {
		return str_replace('Howdy', 'Hello', $translated);
	}
	return $translated;
}
add_filter('gettext', 'launchpad_change_howdy', 10, 3);