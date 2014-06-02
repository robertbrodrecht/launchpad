<?php

/**
 * Metabox Support Functions
 *
 * @package 	Launchpad
 * @since		1.0
 */



/**
 * Enable file upload support for metabox support.
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
 * Add Post Type Meta Boxes
 *
 * @since		1.0
 */
function launchpad_add_meta_boxs() {
	$post_types = get_post_types();
	
	foreach($post_types as $post_type) {
		switch($post_type) {
			case 'attachment':
			case 'revision':
			case 'nav_menu_item':
			break;
			default:
				add_meta_box(
					'launchpad-seo',
					'SEO and Social Media Options',
					'launchpad_seo_meta_box_handler',
					$post_type,
					'advanced',
					'core'
				);
			break;
		}
	}
	
	$post_types = launchpad_get_post_types();
	
	if(!$post_types) {
		return;
	}
	
	foreach($post_types as $post_type => $post_type_details) {
		if(isset($post_type_details['metaboxes']) && $post_type_details['metaboxes']) {
			foreach($post_type_details['metaboxes'] as $metabox_id => $metabox_details) {
				add_meta_box(
					$metabox_id,
					$metabox_details['name'],
					'launchpad_meta_box_handler',
					$post_type,
					$metabox_details['location'],
					$metabox_details['position'],
					$metabox_details
				);
			}
		}
		if(isset($post_type_details['flexible']) && $post_type_details['flexible']) {
			foreach($post_type_details['flexible'] as $flex_id => $flex_details) {
				add_meta_box(
					$flex_id,
					$flex_details['name'],
					'launchpad_flexible_handler',
					$post_type,
					$flex_details['location'],
					$flex_details['position'],
					$flex_details
				);
			}
		}
	}
}
add_action('add_meta_boxes', 'launchpad_add_meta_boxs', 10, 1);


/**
 * Save launchpad_meta fields
 *
 * @param		number $post_id The post ID that the meta applies to
 * @since		1.0
 */
function launchpad_save_post_data($post_id) {
	// Touch the API file to reset the appcache.
	// This helps avoid confusing issues with time zones.
	touch(launchpad_get_cache_file(), time(), time());
	
	// If there is no LaunchPad fields, don't affect anything.
	if(empty($_POST) || !isset($_POST['launchpad_meta'])) {
		return;
	}
	if($_POST['post_type'] === 'page') {
		if(!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if(!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	
	foreach($_POST['launchpad_meta'] as $meta_key => $meta_value) {
		update_post_meta($post_id, $meta_key, $meta_value);
	}
}
add_action('save_post', 'launchpad_save_post_data');


/**
 * Meta Box Handler
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @since		1.0
 */
function launchpad_meta_box_handler($post, $args) {
	
	foreach($args['args']['fields'] as $k => $v) {
		?>
		<div class="launchpad-metabox-field">
			<?php
			
			$generic_help = launchpad_get_field_help($v['args']['type']);
			
			if(!isset($v['help'])) {
				$v['help'] = '';
			}
			
			$v['help'] .= $generic_help;
			
			if($v['help']) {
				?>
				<div class="launchpad-inline-help">
					<span>?</span>
					<div>
					<?php 
						
						echo $v['help']; 
						
					?>
					</div>
				</div>
				<?php
			}
			
			?>
			<label>
				<?php 
					
					echo $v['name']; 
					$v['args']['name'] = $k;
					
					if($post->$k && get_post_meta($post->ID, $k)) {
						$v['args']['value'] = get_post_meta($post->ID, $k, true);
					}
					
				?>
				<?php launchpad_render_form_field($v['args'], false, 'launchpad_meta'); ?>
			</label>
		</div>
	
		<?php
	}
}


/**
 * Flexible Content Handler
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @since		1.0
 */
function launchpad_flexible_handler($post, $args) {
	$current_meta = get_post_meta($post->ID, $args['id'], true);

	?>
		<div id="launchpad-flexible-container-<?php echo $args['id'] ?>" class="launchpad-flexible-container">
			<input type="hidden" name="launchpad_meta[<?php echo $args['id'] ?>]">
			<?php
			
			if($current_meta) {
				foreach($current_meta as $meta_k => $meta_v) {
					foreach($meta_v as $k => $v) {
						echo launchpad_get_flexible_field(
							$args['id'],
							$k,
							$post->ID,
							$v
						);
					}
				}
			}
			
			?>
		</div>
		<div class="launchpad-flexible-add">
			<div>
				<button type="button" class="button">Add Content Module</button>
				<ul>
					<?php
					
					foreach($args['args']['modules'] as $k => $v) {
						echo '<li><a href="#" class="launchpad-flexible-link" data-launchpad-flexible-type="' . $args['id'] . '" data-launchpad-flexible-name="' . $k . '" data-launchpad-flexible-post-id="' . $post->ID . '" title="' . sanitize_text_field($v['help']) . '"><span class="' . ($v['icon'] ? $v['icon'] : 'dashicons dashicons-plus-alt') . '"></span> ' . $v['name'] . '</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	<?php
}


/**
 * Default Flexible Modules
 * 
 * @since		1.0
 */
function launchpad_get_default_flexible_modules() {
	$return = array(
		'accordion' => array(
			'name' => 'Accordion List',
			'icon' => 'dashicons dashicons-list-view',
			'help' => '<p>Creates an accordion list.  This allows for a title the user can click on to view associated content.</p>',
			'fields' => array(
				'title' => array(
					'name' => 'Title',
					'help' => '<p>A title to the accordion section.</p>',
					'args' => array(
						'type' => 'text'
					)
				),
				'description' => array(
					'name' => 'Accordion Description',
					'help' => '<p>A WYSIWYG editor to control the content that appears above the accordion list.</p>',
					'args' => array(
						'type' => 'wysiwyg'
					)
				),
				'accordion' => array(
					'name' => 'Accordion Item',
					'help' => '<p>A single accordion item with a title and content.</p>',
					'args' => array(
						'type' => 'repeater',
						'subfields' => array(
							'title' => array(
								'name' => 'Title',
								'help' => '<p>Title of the accordion item.  The title is displayed as part of a list.  Clicking the title will show the description.</p>',
								'args' => array(
									'type' => 'text'
								)
							),
							'description' => array(
								'name' => 'Description',
								'help' => '<p>The description associated with the title.</p>',
								'args' => array(
									'type' => 'wysiwyg'
								)
							)
						)
					)
				)
			)
		),
		'link_list' => array(
			'name' => 'Link List',
			'icon' => 'dashicons dashicons-admin-links',
			'help' => '<p>Used to create a list of links to internal pages.</p>',
			'fields' => array(
				'title' => array(
					'name' => 'Title',
					'help' => '<p>A title to the link list section.</p>',
					'args' => array(
						'type' => 'text'
					)
				),
				'description' => array(
					'name' => 'Link List Description',
					'help' => '<p>A WYSIWYG editor to control the content that appears above the link list.</p>',
					'args' => array(
						'type' => 'wysiwyg'
					)
				),
				'links' => array(
					'name' => 'Link List Items',
					'help' => '<p>The items to display in the actual link list.</p>',
					'args' => array(
						'type' => 'relationship',
						'post_type' => 'any',
						'limit' => 25
					)
				)
			)
		),
		'section_navigation' => array(
			'name' => 'Section Navigation',
			'icon' => 'dashicons dashicons-welcome-widgets-menus',
			'help' => '<p>Allows for creating a section navigation of the top-level parent\'s children.</p>',
			'fields' => array(
				'title' => array(
					'name' => 'Title',
					'help' => '<p>A title for the navigation.</p>',
					'args' => array(
						'type' => 'text'
					)
				),
				'start' => array(
					'name' => 'Starting Point',
					'help' => '<p>Pick where the menu should start.</p>',
					'args' => array(
						'type' => 'select',
						'options' => array(
							0 => 'Top-level ancestor',
							1 => 'Parent of current page',
						)
					)
				),
				'depth' => array(
					'name' => 'Depth',
					'help' => '<p>How many levels deep should the menu go?</p>',
					'args' => array(
						'type' => 'select',
						'options' => array(
							0 => 'Only show direct children',
							1 => 'Children and grand-children',
							2 => 'Children, grand-children, and great-grand-children'
						)
					)
				),
			)
		),
		'simple_content' => array(
			'name' => 'Simple Content',
			'icon' => 'dashicons dashicons-text',
			'help' => '<p>Allows for adding additional simple content editors with a heading.</p>',
			'fields' => array(
				'title' => array(
					'name' => 'Title',
					'help' => '<p>A title to the content section.</p>',
					'args' => array(
						'type' => 'text'
					)
				),
				'editor' => array(
					'name' => 'Editor',
					'help' => '<p>A WYSIWYG editor to control the content.</p>',
					'args' => array(
						'type' => 'wysiwyg'
					)
				)
			)
		),
	);
	
	$return = apply_filters('launchpad_modify_default_flexible_modules', $return);
	
	return $return;
}




/**
 * Get Flexible Content Layout
 *
 * @since		1.0
 */
function launchpad_get_flexible_field($type = false, $field_name = false, $post_id = false, $values = array()) {
	$is_ajax = false;
	
	if(!$type) {
		header('Content-type: text/html');
		
		$is_ajax = true;
		$type = $_GET['type'];
		$name = $_GET['name'];
		$post_id = $_GET['id'];
		$field_name = $_GET['name'];
	}
	
	$post_types = launchpad_get_post_types();
	$post = get_post($post_id);
	
	if(
		!$post_types || 
		!$post || 
		!$post_types[$post->post_type] || 
		!$post_types[$post->post_type]['flexible'] ||
		!$post_types[$post->post_type]['flexible'][$type] || 
		!$post_types[$post->post_type]['flexible'][$type]['modules'] || 
		!$post_types[$post->post_type]['flexible'][$type]['modules'][$field_name]
	) {
		$ret = '';
	}
	
	
	$details = $post_types[$post->post_type]['flexible'][$type]['modules'][$field_name];
	
	ob_start();
	
	echo '<div class="launchpad-flexible-metabox-container"><a href="#" onclick="jQuery(this).parent().remove(); return false;" class="launchpad-flexible-metabox-close">&times;</a>';
	
	if($details['help']) {
		?>
		<div class="launchpad-inline-help">
			<span>?</span>
			<div><?php echo $details['help']; ?></div>
		</div>
		<?php
	}
	echo '<div class="handlediv" onclick="jQuery(this).parent().toggleClass(\'closed\')"><br></div>';
	echo '<h3>' . $details['name'] . '</h3>';
	
	$flex_uid = preg_replace('/[^A-Za-z0-9\-\_]/', '', $field_name . '-' . uniqid());
	
	foreach($details['fields'] as $sub_field_name => $field) {
		$use_label = true;
		
		switch($field['args']['type']) {
			case 'wysiwyg':
			case 'repeater':
				$use_label = false;
			break;
		}
		
		$id = preg_replace('/[^A-Za-z0-9]/', '', $field_name . '' . $sub_field_name . '' . uniqid());
		
		echo '<div class="launchpad-metabox-field">';
		
		$generic_help = launchpad_get_field_help($field['args']['type']);
		
		if(!isset($field['help'])) {
			$field['help'] = '';
		}
		
		$field['help'] .= $generic_help;
		
		$help = $field['help'];
		
		if($field['args']['type'] === 'repeater') {
			$help .= '<p>Available fields:</p><dl>';
			
			foreach($field['args']['subfields'] as $subfield_detail) {
				$help .= '<dt>' . $subfield_detail['name'] . '</dt>';
				if($subfield_detail['help']) {
					$help .= '<dd>' . $subfield_detail['help'] . '</dd>';
				} else {
					$help .= '<dd><p>No information provided.</p></dd>';					
				}
			}
			$help .= '</dl>';
		}
		
		if($help) {
			?>
			<div class="launchpad-inline-help">
				<span>?</span>
				<div><?php echo $help; ?></div>
			</div>
			<?php
		}
		
		if($use_label) {
			echo '<label for="' . $id . '">' . $field['name'] . '</label>';
		}
		
		if($values) {
			$field['args']['value'] = $values[$sub_field_name];
		}
		
		launchpad_render_form_field(
				array_merge(
					$field['args'], 
					array(
						'name' => 'launchpad_meta[' . $type . '][' . $flex_uid . '][' . $field_name . '][' . $sub_field_name . ']',
						'id' => $id,
						'label' => $field['name']
					)
				), 
				false, 
				'launchpad_flexible'
			);
		
		echo '</div>';
		
	}
	
	echo '</div>';
		
	$ret = ob_get_contents();
	ob_clean();
	
	if($is_ajax) {
		echo $ret;
		exit;
	} else {
		return $ret;
	}
}
add_action('wp_ajax_get_flexible_field', 'launchpad_get_flexible_field');
add_action('wp_ajax_nopriv_get_flexible_field', 'launchpad_get_flexible_field');


/**
 * Get Visual Editor Code
 *
 * @since		1.0
 */
function launchpad_get_editor() {
	wp_editor(
			'', 
			$_GET['id'],
			array(
				'wpautop' => true,
				'media_buttons' => true,
				'textarea_name' => $_GET['name'],
				'textarea_rows' => 10,
				'tinymce' => true,
				'drag_drop_upload' => true
			)
		);
	exit;
}
add_action('wp_ajax_get_editor', 'launchpad_get_editor');
add_action('wp_ajax_nopriv_get_editor', 'launchpad_get_editor');


/**
 * AJAX Post Filter
 *
 * @since		1.0
 */
function launchpad_get_post_list() {
	header('Content-type: application/json');
	
	$_GET['terms'] = trim($_GET['terms']);
	
	if($_GET['terms']) {
		$res = new WP_Query(
				array(
					'post_type' => $_GET['post_type'],
					's' => $_GET['terms']
				)
			);
	} else {
		$res = new WP_Query(
				array(
					'post_type' => $_GET['post_type'],
					'posts_per_page' => 25
				)
			);
	}

	$ret = array();
	
	foreach($res->posts as $p) {
		$ancestors = get_post_ancestors($p);
		
		$small = '';
		
		if($ancestors) {
			$ancestors = array_reverse($ancestors);
			foreach($ancestors as $key => $ancestor) {
				$ancestor = get_post($ancestor);
				$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
			}
		}
		
		$p->ancestor_chain = $small;
		$ret[] = $p;
	}
		
	echo json_encode($ret);
	exit;
}
add_action('wp_ajax_search_posts', 'launchpad_get_post_list');
add_action('wp_ajax_nopriv_search_posts', 'launchpad_get_post_list');
