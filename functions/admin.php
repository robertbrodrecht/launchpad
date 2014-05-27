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
 * Enable file uploads.
 *
 * @since		1.0
 */
function launchpad_enable_media_upload() {
	global $post; 
	
	wp_enqueue_script(
		array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',
			'jquery-ui-sortable',
			'wp-color-picker',
			'thickbox',
			'media-upload'	
		)
	);
	
	wp_enqueue_style(
		array(
			'thickbox',
			'wp-color-picker'
		)
	);
	
	
	if(function_exists('wp_enqueue_media') && !did_action('wp_enqueue_media')){
		wp_enqueue_media();
	}
}
add_action('admin_enqueue_scripts', 'launchpad_enable_media_upload');


/**
 * Activate the Style Selector
 *
 * @since		1.0
 */
function launchpad_activate_style_select( $buttons ) {
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
add_filter('mce_buttons_2', 'launchpad_activate_style_select');


/**
 * Add Common Styles.
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
 * Add Custom Image Sizes to Admin Selector
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
 * Generate OPTIONs based on an array
 *
 * @param		array $options The array of options to build
 * @param		array $values The array of values to pick selected options
 * @since		1.0
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
 * @since		1.0
 */
function launchpad_render_form_field($args, $subfield = false, $field_prefix = 'launchpad_site_options') {
	if($field_prefix === 'launchpad_site_options') {
		$vals = get_option('launchpad_site_options', '');
		if(isset($vals[$args['name']]))  {
			$val = $vals[$args['name']];
		} else {
			$val = isset($args['default']) ? $args['default'] : '';
		}
	} else {
		$val = $args['value'];
		if(!$val && $val !== '' && isset($args['default'])) {
			$val = $args['default'];
		}
	}
	
	$class = 'launchpad-field-' . $field_prefix;
	
	if($subfield) {
		$class .= ' launchpad-subfield ' . sanitize_title($subfield);
	}
	
	
	if($field_prefix !== 'launchpad_flexible') {
		$field_output_name = $field_prefix . '[' . $args['name'] . ']';
	} else {
		$field_output_name = $args['name'];
	}
	
	if($args['id']) {
		$field_output_id = $args['id'];
	} else {
		$field_output_id = $args['name'];
	}
	
	$field_output_id = sanitize_title($field_output_id);
	
	switch($args['type']) {
		case 'checkbox':
			echo '<input type="hidden" name="' . $field_output_name . '" value="">';
			if($subfield) {
				echo '<label class="' . $class . '">';
			}
			echo '<input type="checkbox" name="' . $field_output_name . '" id="' . $field_output_id . '" ' . ($val ? ' checked="checked"' : '') . '>';
			if($subfield) {
				echo ' ' . $subfield . '</label>';
			}
		break;
		case 'file':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			
			$existing = wp_get_attachment_image($val);
			if(!$existing) {
				$val = '';
			}
			
			echo '<input type="hidden" name="' . $field_output_name . '" id="' . $field_output_id . '" value="' . $val . '" class="regular-text"><button type="button" class="launchpad-full-button launchpad-file-button button insert-media add_media" data-for="' . $field_output_id . '" class="file-button">Upload File</button>';
			if($existing) {
				echo '<br><a href="#" class="launchpad-delete-file" onclick="document.getElementById(\'' . $field_output_id . '\').value=\'\'; this.parentNode.removeChild(this); return false;">' . $existing . '</a>';
			}
			
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'text':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			echo '<input type="text" name="' . $field_output_name . '" id="' . $field_output_id . '" value="' . $val . '" class="regular-text">';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'textarea':
			echo '<textarea name="' . $field_output_name . '" id="' . $field_output_id . '" rows="10" cols="50" class="large-text code">' . $val . '</textarea>';
		break;
		case 'wysiwyg':
			wp_editor(
					$val, 
					$field_output_id,
					array(
						'wpautop' => true,
						'media_buttons' => true,
						'textarea_name' => $field_output_name,
						'textarea_rows' => 10,
						'tinymce' => true,
						'drag_drop_upload' => true
					)
				);
		break;
		case 'select':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			echo '<select name="' . $field_output_name . '" id="' . $field_output_id . '">';
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
			echo '<select name="' . $field_output_name . '" size="10" multiple="multiple" id="' . $field_output_id . '">';
			echo launchpad_create_select_options($args['options'], $val);
			echo '</select>';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'menu':
			if($subfield) {
				echo '<label class="' . $class . '">' . $subfield . ' ';
			}
			
			$all_menus = get_terms('nav_menu', array('hide_empty' => true));
			
			$menu_list = array();
			foreach($all_menus as $menu) {
				$menu_list[$menu->term_id] = $menu->name;
			}
			
			echo '<select name="' . $field_output_name . '" id="' . $field_output_id . '">';
			echo '<option value="">Select One</option>';
			echo launchpad_create_select_options($menu_list, $val);
			echo '</select>';
			if($subfield) {
				echo '</label>';
			}
		break;
		case 'relationship':		
			echo '<div class="launchpad-relationship-container" data-post-type="' . $args['post_type'] . '" data-field-name="' . $field_output_name . '[]" data-limit="' . $args['limit'] . '">';
			echo '<div class="launchpad-relationship-search"><label><input type="search" class="launchpad-relationship-search-field" placeholder="Search"></label><ul class="launchpad-relationship-list">';
			
			$preload = new WP_Query(
					array(
						'post_type' => $args['post_type'],
						'posts_per_page' => 25,
					)
				);
			
			foreach($preload->posts as $p) {
				$ancestors = get_post_ancestors($p);
				
				$small = '';
				
				if($ancestors) {
					$ancestors = array_reverse($ancestors);
					foreach($ancestors as $key => $ancestor) {
						$ancestor = get_post($ancestor);
						$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
					}
				}
				
				echo '<li><a href="#" data-id="' . $p->ID . '">' . $p->post_title . ' <small>' . $small . '</small></a></li>';
			}
			
			echo '</ul></div>';
			echo '<div class="launchpad-relationship-items-container"><strong> Saved Items (';
			if($args['limit'] > -1) {
				echo 'Maximum of ' . $args['limit'] . ' item' . ($args['limit'] == 1 ? '' : 's');
			} else {
				echo 'Add as many as you like';
			}
			echo ')</strong><ul class="launchpad-relationship-items">';
			
			if($val) {
				foreach($val as $post_id) {
					$post_id = get_post($post_id);
					
					$ancestors = get_post_ancestors($post_id);
					
					$small = '';
					
					if($ancestors) {
						$ancestors = array_reverse($ancestors);
						foreach($ancestors as $key => $ancestor) {
							$ancestor = get_post($ancestor);
							$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
						}
					}
					
					echo '<li><a href="#" data-id="' . $post_id->ID . '"><input type="hidden" name="' . $field_output_name . '[]" value="' . $post_id->ID . '">' . $post_id->post_title . ' <small>' . $small . '</small></a></li>';
				}
			}
			
			echo '</ul></div>';
			echo '</div>';
		break;
		case 'taxonomy':
			echo '</label><input type="hidden" name="' . $field_output_name . '">';
			if(!is_array($args['taxonomy'])) {
				$args['taxonomy'] = explode(',', preg_replace('/\s?,\s?/', ',', $args['taxonomy']));
			}
			
			if(!$val) {
				$val = array();
			}
			
			foreach($args['taxonomy'] as $tax) {
				if(taxonomy_exists($tax)) {
					$terms = get_terms($tax, array('hide_empty' => false));
					$tax = get_taxonomy($tax);
					echo '<fieldset class="launchpad-metabox-fieldset"><legend>' . $tax->labels->name . '</legend>';
					foreach($terms as $term) {
						echo '<div class="launchpad-metabox-field"><label>';
						if($args['multiple']) {
							echo '<input type="checkbox" name="' . $field_output_name . '[]" value="' . $term->term_id . '"' . (in_array($term->term_id, $val) ? ' checked="checked"' : '') . '>';
						} else {
							echo '<input type="radio" name="' . $field_output_name . '[]" value="' . $term->term_id . '"' . (in_array($term->term_id, $val) ? ' checked="checked"' : '') . '>';		
						}
						echo $term->name;
						echo '</label></div>';
					}
					echo '</fieldset>';
				}
			}
		break;
		case 'repeater':
			$repeater_tmp_id = uniqid();
			
			if($val) {
				$orig_subfield = $args['subfields'];
				$args['subfields'] = array();
				while($val) {
					$tmp_subfield = $orig_subfield;
					$tmp_vals = array_shift($val);
					foreach($tmp_vals as $tmp_key => $tmp_val) {
						if(isset($tmp_subfield[$tmp_key])) {
							$tmp_subfield[$tmp_key]['args']['value'] = $tmp_val;
						}
					}
					array_push($args['subfields'], $tmp_subfield);
				}
			} else {
				$args['subfields'] = array($args['subfields']);
			}
			
			echo '<div id="launchpad-' . $repeater_tmp_id . '-repeater" class="launchpad-repeater-container launchpad-metabox-field">';
			
			foreach($args['subfields'] as $counter => $sub_fields) {
				echo '<div class="launchpad-flexible-metabox-container launchpad-repeater-metabox-container">'; 
				echo '<div class="handlediv" onclick="jQuery(this).parent().toggleClass(\'closed\')"><br></div>';
				echo '<a href="#" onclick="jQuery(this).parent().remove(); return false;" class="launchpad-flexible-metabox-close">&times;</a>';
				echo '<h3>' . $args['label'] . '</h3>';
					
				foreach($sub_fields as $field_key => $field) {
				
					echo '<div class="launchpad-metabox-field">';
					
					launchpad_render_form_field(
							array_merge($field['args'], array('name' => $field_output_name . '[launchpad-' . $repeater_tmp_id . $counter . '-repeater][' . $field_key . ']')), 
							$field['name'], $field_prefix
						);
					echo '</div>';
				}
	
				echo '</div>';
			}
			
			echo '</div>';
			
			echo '<button type="button" class="button launchpad-repeater-add" data-for="launchpad-' . $repeater_tmp_id . '-repeater">Add Additional ' . $args['label'] . '</button>';
		break;
		case 'subfield':
			foreach($args['subfields'] as $field) {
				launchpad_render_form_field($field['args'], $field['name']);
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

	<style type="text/css">
		html,
		body.login {
			overflow: auto;
		}
		
		body.login {
			background: #333;
			/* background-image: repeating-linear-gradient(-45deg, transparent, transparent 1px, rgba(0,0,0,.05) 1px, rgba(0,0,0,.05) 7px); */
		}

		body.login:after,		
		body.login:before {
			content: ' ';
			display: block;
			height: 80px;
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
		.login #nav a:hover, 
		.login #backtoblog a:hover,
		.login #nav a:focus,
		.login #backtoblog a:focus,
		a:active {
			color: <?php echo $site_options['primary_color'] ? $site_options['primary_color'] : '#333' ?>;
		}
		
		.wp-core-ui .button-primary,
		.wp-core-ui .button-primary:hover,
		.wp-core-ui .button-primary:focus,
		.wp-core-ui .button-primary:active {
			background: <?php echo $site_options['primary_color'] ? $site_options['primary_color'] : '#333' ?>;
			border: 0;
			border-radius: 0;
			box-shadow: none;
		}
		.wp-core-ui .button-primary:hover,
		.wp-core-ui .button-primary:focus,
		.wp-core-ui .button-primary:active {
			opacity: .75;
		}
		#login {
			background: #F6F6F6;
			/* background-image: repeating-linear-gradient(45deg, transparent, transparent 1px, rgba(0,0,0,.02) 1px, rgba(0,0,0,.02) 7px); */
			border: 15px solid rgba(0, 0, 0, .85);
			border-radius: 20px;
			padding: 20px;
			width: auto;
			max-width: 480px;
		}
		.login form {
			border-radius: 10px;
			box-shadow: none;
			margin-bottom: 12px;
			<?php if($site_options['secondary_color']) { ?>
				border: 1px solid <?php echo $site_options['secondary_color']; ?>
			<?php } ?>
		}
		
		.login #nav {
			float: right;
			margin: 0;
			padding: 0;
		}
		.login #backtoblog {
			margin: 0;
			padding: 0;
		}
		
		.login .message,
		.login #login_error {
			background: #EFEFEF;
			box-shadow: none;
			border: 1px solid gray;
			margin-left: 24px;
			margin-right: 24px;
			text-align: center;
		}
		
	</style>
	
<?php 

}
add_action('login_enqueue_scripts', 'launchpad_custom_login');