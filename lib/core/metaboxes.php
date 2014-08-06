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
	
	// These scripts must be included to handle media uploading.
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
	
	// These styles must be included to handle media uploading.
	wp_enqueue_style(
		array(
			'thickbox',
			'wp-color-picker'
		)
	);
	
	// Equeue media if it hasn't already been done.
	if(function_exists('wp_enqueue_media') && !did_action('wp_enqueue_media')){
		wp_enqueue_media();
	}
}
if(is_admin()) {
	add_action('admin_enqueue_scripts', 'launchpad_enable_media_upload');
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
	
	// Loop the options.
	foreach($options as $option_value => $option_text) {
		// If the option is an array, we'll create an optgroup.
		// We'll just call the function recursively to get those options.
		if(is_array($option_text)) {
			$ret .= '<optgroup label="' . ucwords($option_value) . '">';
			$ret .= launchpad_create_select_options($option_text, $values);
			$ret .= '</optgroup>';
		
		// Otherwise, create the individual options.
		} else {
			$ret .= '<option value="' . $option_value . '"' . (is_array($values) ? (in_array($option_value, $values) ? ' selected="selected"' : '') : ($values == $option_value ? ' selected="selected"' : '')) . '>' . $option_text . '</option>';
		}
	}
	return $ret;
}


/**
 * Render A Checkbox
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_checkbox($field_output_name, $field_output_id = '', $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// Checkboxes need an empty hidden field by default.
	// This allows for unchecked checkboxes to have an empty value.
	// Because PHP is good for the job, a checked checkbox overwrites 
	// the empty value in the querystring array.
	echo '<input type="hidden" name="' . $field_output_name . '" value="">';
	if($subfield) {
		echo '<label class="' . $class . '">';
	}
	echo '<input type="checkbox" name="' . $field_output_name . '" id="' . $field_output_id . '" ' . ($val ? ' checked="checked"' : '') . '>';
	if($subfield) {
		echo ' ' . $subfield . '</label>';
	}
}


/**
 * Render A Input Text
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		array $args The arguments on the field.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_text($field_output_name, $field_output_id = '', $args, $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// Text is pretty simple.  Just output the field.
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	echo '<input type="text" name="' . $field_output_name . '" id="' . $field_output_id . '" value="' . $val . '" class="regular-text"' . ((int) $args['maxlength'] ? ' maxlength="' . (int) $args['maxlength'] . '"' : '') . '>';
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A Textarea
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		array $args The arguments on the field
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_textarea($field_output_name, $field_output_id = '', $args, $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// Textarea is pretty simple.  Just output the field.
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	echo '<textarea name="' . $field_output_name . '" id="' . $field_output_id . '" rows="10" cols="50" class="large-text code"' . ((int) $args['maxlength'] ? ' maxlength="' . (int) $args['maxlength'] . '"' : '') . '>' . $val . '</textarea>';
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A Select Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		string $options The options to populate.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_select($field_output_name, $field_output_id = '', $options = array(), $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// Output a select and pass options to launchpad_create_select_options()
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	
	// Create the select.
	echo '<select name="' . $field_output_name . '" id="' . $field_output_id . '">';
	echo '<option value="">Select One</option>';
	echo launchpad_create_select_options($options, $val);
	echo '</select>';
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A Select Multiple Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		string $options The options to populate.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_selectmulti($field_output_name, $field_output_id = '', $options = array(), $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// Output a select and pass options to launchpad_create_select_options()
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	
	// The empty hidden field allows the user to actually select none.
	echo '<input type="hidden" name="' . $field_output_name . '" value="">';
	
	// Create the select.
	echo '<select name="' . $field_output_name . '[]" size="10" multiple="multiple" id="' . $field_output_id . '">';
	echo launchpad_create_select_options($options, $val);
	echo '</select>';
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A WordPress File Input
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_file($field_output_name, $field_output_id = '', $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// For file, output a thubmnail if the image saved.
	// Add a hidden field with the ID.
	// JavaScript will handle updating the hidden field when a photo is selected.
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	
	if((int) $val > 0) {
		$img = wp_get_attachment_image((int) $val, 'thumbnail', true);
		$existing = wp_get_attachment_metadata((int) $val);
		
		if(!$img && $existing) {
			$existing = 'Preview Not Available';
		} else {
			$existing = $img;
		}
	} else {
		$existing = false;
	}
	if(!$existing) {
		$val = '';
	}
	
	// The file ID is stored here.
	echo '<input type="hidden" name="' . $field_output_name . '" id="' . $field_output_id . '" value="' . $val . '" class="regular-text"><button type="button" class="launchpad-full-button launchpad-file-button button insert-media add_media" data-for="' . $field_output_id . '" class="file-button">Upload File</button>';
	
	// If there is an existing image, add a "remove" button.
	if($existing) {
		echo '<br><a href="#" class="launchpad-delete-file" onclick="document.getElementById(\'' . $field_output_id . '\').value=\'\'; this.parentNode.removeChild(this); return false;">' . $existing . '</a>';
	}
	
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A WordPress WYSIWYG Editor
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		bool $val Whether the checkbox is checked.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_wysiwyg($field_output_name, $field_output_id = '', $val = false) {
	// Output a WYSIWYG editor.  Just the base code.
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
}


/**
 * Render A Menu Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_menu($field_output_name, $field_output_id = '', $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	// The menu field is essentially the same for select except we automagially pre-populate
	// the select with the saved menus.
	if($subfield) {
		echo '<label class="' . $class . '">' . $subfield . ' ';
	}
	
	// Grab all nav menus.
	$all_menus = get_terms('nav_menu', array('hide_empty' => true));
	
	// Put the nav menus in an array to convert into options.
	$menu_list = array();
	foreach($all_menus as $menu) {
		$menu_list[$menu->term_id] = $menu->name;
	}
	
	// Create the select.
	echo '<select name="' . $field_output_name . '" id="' . $field_output_id . '">';
	echo '<option value="">Select One</option>';
	echo launchpad_create_select_options($menu_list, $val);
	echo '</select>';
	if($subfield) {
		echo '</label>';
	}
}


/**
 * Render A Relationship Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $post_type The post type to use.  Maybe try "any" if you want all types.
 * @param		int $limit The max number of fields to allow.
 * @param		bool $val Whether the checkbox is checked.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_relationship($field_output_name, $post_type = '', $limit = -1, $val = false) {
	$field_output_name = trim($field_output_name);
	$post_type = trim($post_type);
	if(!$field_output_name) {
		return;
	}
	
	if($post_type === '') {
		$post_type = 'any';
	}
	
	// This field is quite complex.  A lot of the functionality is handeled via JavaScript
	// and the search_posts API that is driven by launchpad_get_post_list().
	
	// Field container.
	echo '<div class="launchpad-relationship-container" data-post-type="' . $post_type . '" data-field-name="' . $field_output_name . '[]" data-limit="' . $limit . '">';
	
	// Post list container.
	echo '<div class="launchpad-relationship-search"><label><input type="search" class="launchpad-relationship-search-field" placeholder="Search"></label><ul class="launchpad-relationship-list">';
	
	// Select the most recent 25 items in the post type.
	$preload = new WP_Query(
			array(
				'post_type' => $post_type,
				'posts_per_page' => 25,
			)
		);
	
	// Loop each.
	foreach($preload->posts as $p) {
		// Get a list of acnestors.
		$ancestors = get_post_ancestors($p);
		
		// This will keep the ancestor hierarchy.
		$small = '';
		
		// If there are ancestors, loop them and add them to the small.
		if($ancestors) {
			$ancestors = array_reverse($ancestors);
			foreach($ancestors as $key => $ancestor) {
				$ancestor = get_post($ancestor);
				$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
			}
		}
		
		// Output the option.
		echo '<li><a href="#" data-id="' . $p->ID . '">' . $p->post_title . ' <small>' . $small . '</small></a></li>';
	}
	
	// Close post list container.
	echo '</ul></div>';
	
	// Add a note about how many items can go in the list.
	if($limit > 0) {
		$tmp_item_count_note = 'Maximum of ' . $limit . ' item' . ($limit == 1 ? '' : 's');
	} else {
		$tmp_item_count_note = 'Add as many as you like';
	}
	
	// "Selected" list container.
	echo '<div class="launchpad-relationship-items-container">';
	echo '<strong> Saved Items (' . $tmp_item_count_note . ')</strong>';
	echo '<ul class="launchpad-relationship-items">';
	
	// If there are values, we need to populate them.
	if($val) {
		// Loop the values.
		foreach($val as $post_id) {
			// Get the post for the value.
			$post_id = get_post($post_id);
			
			// Get the post acncestors for the value.
			$ancestors = get_post_ancestors($post_id);
			
			// This will keep the ancestor hierarchy.
			$small = '';
			
			// If there are ancestors, loop them and add them to the small.
			if($ancestors) {
				$ancestors = array_reverse($ancestors);
				foreach($ancestors as $key => $ancestor) {
					$ancestor = get_post($ancestor);
					$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
				}
			}
			
			// Output the option.
			echo '<li><a href="#" data-id="' . $post_id->ID . '"><input type="hidden" name="' . $field_output_name . '[]" value="' . $post_id->ID . '">' . $post_id->post_title . ' <small>' . $small . '</small></a></li>';
		}
	}
	
	// Close the "selected" list container.
	echo '</ul></div>';
	
	// Close the field container.
	echo '</div>';
}


/**
 * Render A Taxonomy Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $taxonomy The taxonomy to use.
 * @param		bool $mutliple Whether you can select multiple values.
 * @param		bool $val Whether the checkbox is checked.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_taxonomy($field_output_name, $taxonomy = false, $multiple = false, $val = false) {
	$field_output_name = trim($field_output_name);
	$taxonomy = trim($taxonomy);
	if(!$field_output_name) {
		return;
	}
	
	if($taxonomy === false) {
		return;
	}
	
	// The empty hidden field, much like with the checkbox field, is needed to allow
	// for saving with no checked items.
	echo '<input type="hidden" name="' . $field_output_name . '">';
	
	// If we weren't given an array of taxonomies, split on commas.
	if(!is_array($taxonomy)) {
		$taxonomy = explode(',', preg_replace('/\s?,\s?/', ',', $taxonomy));
	}
	
	// If we have no values, create an empty array.
	if(!$val) {
		$val = array();
	}
	
	// Loop the taxonomies.
	foreach($taxonomy as $tax) {
		// If there is a taxonomy for the slug, we can present it to the user.
		if(taxonomy_exists($tax)) {
			
			// Get all the terms for the taxonomy.
			$terms = get_terms($tax, array('hide_empty' => false));
			
			// Get the taxonomy itself.
			$tax = get_taxonomy($tax);
			
			// Create a fieldset to house the checkboxes.
			echo '<fieldset class="launchpad-metabox-fieldset"><legend>' . $tax->labels->name . '</legend>';
			
			if(!$terms) {
				echo '<p>No "' . $tax->labels->name . '" terms have been set.</p>';
			}
			
			// Loop the terms.
			foreach($terms as $term) {
				echo '<div class="launchpad-metabox-field"><label>';
				
				// If the user can select multiple items, output checkboxes.
				if($multiple) {
					echo '<input type="checkbox" name="' . $field_output_name . '[]" value="' . $term->term_id . '"' . (in_array($term->term_id, $val) ? ' checked="checked"' : '') . '>';
				
				// If not, output radios.
				} else {
					echo '<input type="radio" name="' . $field_output_name . '[]" value="' . $term->term_id . '"' . (in_array($term->term_id, $val) ? ' checked="checked"' : '') . '>';		
				}
				echo $term->name;
				echo '</label></div>';
			}
			echo '</fieldset>';
		}
	}
}


/**
 * Render A Repeater Field
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		array $subfields The fields that go in the repeater.
 * @param		string $label The name of the repeater.
 * @param		string $field_prefix The prefix to use on the field name.
 * @param		array $val The values to populate.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_repeater($field_output_name, $subfields, $label, $field_prefix, $val) {
	// Repeaters are complex fields.  A lot of the functionality is handeled via JavaScript.
	
	// This temp ID is used on the button and div to sync up where the repeater
	// JavaScript needs to go to add to the repeater.
	$repeater_tmp_id = uniqid();
	
	// If there are values to populate, we need to do a bit of trickery.
	// And by "trickery" I mean: This is an inelegant hack.
	// Basically, since repeaters are defined with only one set of subfields,
	// we need to convert all the values into duplicates of the subfield
	// so that the script thinks there are multiple subfields with values.
	if($val) {
		// Get a copy of the original subfields for the repeater.
		$orig_subfield = $subfields;
		
		// Overwrite it with an empty array.
		$subfields = array();
		
		// Loop the values.
		while($val) {
			// Create a temporary copy of the original subfields.
			$tmp_subfield = $orig_subfield;
			
			// Shift the first value off of the values.
			$tmp_vals = array_shift($val);
			
			// Loop the values and set the value as the field's value.
			foreach($tmp_vals as $tmp_key => $tmp_val) {
				if(isset($tmp_subfield[$tmp_key])) {
					$tmp_subfield[$tmp_key]['args']['value'] = $tmp_val;
				}
			}
			// Add the value to the subfield.
			array_push($subfields, $tmp_subfield);
		}
		
	// Again, this is kind of a hack.  Since populating the values
	// will "fake" multiple sets of sub fields, we need to normalize
	// to an array of subfields so the developer doesn't have to deal
	// with varying syntax to create repeaters versus other field types.
	} else {
		$subfields = array($subfields);
	}
	
	// Repeater container.  The JavaScript looks for this when handling button clicks.
	echo '<div id="launchpad-' . $repeater_tmp_id . '-repeater" class="launchpad-repeater-container launchpad-metabox-field">';
	
	// Loop all the subfields.
	foreach($subfields as $counter => $sub_fields) {
		// The repeater fields container.
		echo '<div class="launchpad-flexible-metabox-container launchpad-repeater-metabox-container">'; 
		// The "collapse field" handler.
		echo '<div class="handlediv" onclick="jQuery(this).parent().toggleClass(\'closed\')"><br></div>';
		// The remove button.
		echo '<a href="#" onclick="jQuery(this).parent().remove(); return false;" class="launchpad-flexible-metabox-close">&times;</a>';
		// The name of the repeater.
		echo '<h3>' . $label . '</h3>';
		
		// Loop the subfield's fields to handle the output.
		foreach($sub_fields as $field_key => $field) {
			
			// Create a metabox field container.
			echo '<div class="launchpad-metabox-field">';
			
			// Recursively create the field.
			launchpad_render_form_field(
					array_merge(
						$field['args'], 
						array(
							'name' => $field_output_name . '[launchpad-' . 
								$repeater_tmp_id . $counter . '-repeater][' . $field_key . ']'
						)
					), 
					$field['name'], 
					$field_prefix
				);
			
			// Close the metabox field container.
			echo '</div>';
		}
		
		// Close the repeater fields container.
		echo '</div>';
	}
	
	// Close the repeater container.
	echo '</div>';
	
	// Add a button that will be used to add repeater items.
	echo '<button type="button" class="button launchpad-repeater-add" data-for="launchpad-' . $repeater_tmp_id . '-repeater">Add Additional ' . $label . '</button>';
}


/**
 * Render Address Fields
 * 
 * @param		string $field_output_name The field's "name" attribute.
 * @param		string $field_output_id The field's "id" attribute.
 * @param		array $args The arguments on the field.
 * @param		bool $val Whether the checkbox is checked.
 * @param		string $class A class to use on the label if this is a subfield.
 * @param		bool|string $subfield If truthy, creates a label with $subfield as the text.
 * @see			launchpad_render_form_field
 * @since		1.0
 */
function launchpad_render_field_address($field_output_name, $field_output_id = '', $args, $val = false, $class = '', $subfield = false) {
	$field_output_name = trim($field_output_name);
	$field_output_id = trim($field_output_id);
	if(!$field_output_name) {
		return;
	}
	
	if(!$field_output_id) {
		$field_output_id = $field_output_name;
	}
	
	echo '<fieldset class="launchpad-address launchpad-metabox-fieldset"><legend>' . ($args['label'] ? $args['label'] : 'Address Details') . '</legend>';
	
	echo '<div><label><input type="text" name="' . $field_output_name . '[street]" value="' . $val['street'] . '" class="regular-text">Street Address</label></div>';
	echo '<div><label><input type="text" name="' . $field_output_name . '[number]" value="' . $val['number'] . '" class="regular-text">Apartment / Suite Number</label></div>';
	
	echo '<div class="launchpad-csz-container">';

	echo '<div><label><input type="text" name="' . $field_output_name . '[city]" value="' . $val['city'] . '" class="regular-text">City</label></div>';	
	
	echo '<div><label><input type="text" name="' . $field_output_name . '[state]" value="' . $val['state'] . '" class="regular-text" maxlength="2">State</label></div>';	
	
	echo '<div><label><input type="text" name="' . $field_output_name . '[zip]" value="' . $val['zip'] . '" class="regular-text" maxlength="10">Zip</label></div>';
	
	echo '</div>';
	echo '<input type="hidden" name="' . $field_output_name . '[latitude]" value="' . $val['latitude'] . '">';
	echo '<input type="hidden" name="' . $field_output_name . '[longitude]" value="' . $val['longitude'] . '">';
	echo '</fieldset>';
}


/**
 * Render fields
 * 
 * This gets a bit convoluted because it is used to render both launchpad site options fields
 * and launchpad metabox fields.  Site options were developed first, so a lot of the weird 
 * logic at the top is to make this function work for both, which requires some monkeying around.
 *
 * @param		array $args The array of settings
 * @param		string|bool $subfield Whether this is a subfield.  If it is a string, it is the field's label.
 * @param		string $field_prefix What prefix to add: launchpad_site_options, launchpad_meta, or launchpad_flexible.
 * @see			launchpad_get_setting_fields
 * @since		1.0
 */
function launchpad_render_form_field($args, $subfield = false, $field_prefix = 'launchpad_site_options') {
	
	// If we're dealing with site options, handle the set up.
	if($field_prefix === 'launchpad_site_options') {
		// Get the site options.
		$vals = get_option('launchpad_site_options', '');
		// If an option value exists for the current field, set it as $val.
		if(isset($vals[$args['name']]))  {
			$val = $vals[$args['name']];
		// Otherwise, see if there is a default value to set as $val.
		} else {
			$val = isset($args['default']) ? $args['default'] : '';
		}
	// Otherwise, we're dealing with post meta.
	} else {
		// Set the value to the the args value.
		$val = $args['value'];
		// If there is no value and there is a default, set the default as the value.
		if(!$val && $val !== '' && isset($args['default'])) {
			$val = $args['default'];
		}
	}
	
	// Set a class to use based on the field prefix to style against.
	$class = 'launchpad-field-' . $field_prefix;
	
	// If the field is a sub-field, add a class that specifies that it is a subfield
	// and what kind of subfield.
	if($subfield) {
		$class .= ' launchpad-subfield ' . sanitize_title($subfield);
	}
	
	// If we're dealing with flexible content, the field's @name needs to be sandboxed into an array.
	if($field_prefix !== 'launchpad_flexible') {
		$field_output_name = $field_prefix . '[' . $args['name'] . ']';
		
	// Otherwise, it can be whatever the developer wanted it to be.
	} else {
		$field_output_name = $args['name'];
	}
	
	// If there is an ID specified for a field, set it as the @id for the field.
	if($args['id']) {
		$field_output_id = $args['id'];
	
	// Otherwise, fallback to using the @name as the @id.
	} else {
		$field_output_id = $args['name'];
	}
	
	// Sanitize it just in case.
	$field_output_id = sanitize_title($field_output_id);
	
	// Determine how to handle each field based on the type of field it is.
	switch($args['type']) {
		case 'checkbox':
			launchpad_render_field_checkbox($field_output_name, $field_output_id, $val, $class, $subfield);
		break;
		case 'text':
			launchpad_render_field_text($field_output_name, $field_output_id, $args, $val, $class, $subfield);
		break;
		case 'textarea':
			launchpad_render_field_textarea($field_output_name, $field_output_id, $args, $val, $class, $subfield);
		break;
		case 'select':
			launchpad_render_field_select($field_output_name, $field_output_id, $args['options'], $val, $class, $subfield);
		break;
		case 'selectmulti':
			launchpad_render_field_selectmulti($field_output_name, $field_output_id, $args['options'], $val, $class, $subfield);
		break;
		case 'file':
			launchpad_render_field_file($field_output_name, $field_output_id, $val, $class, $subfield);
		break;
		case 'wysiwyg':
			launchpad_render_field_wysiwyg($field_output_name, $field_output_id, $val);
		break;
		case 'menu':
			launchpad_render_field_menu($field_output_name, $field_output_id, $val, $class, $subfield);
		break;
		case 'relationship':
			launchpad_render_field_relationship($field_output_name, $args['post_type'], $args['limit'], $val);
		break;
		case 'taxonomy':
			launchpad_render_field_taxonomy($field_output_name, $args['taxonomy'], $args['multiple'], $val);
		break;
		case 'repeater':
			launchpad_render_field_repeater($field_output_name, $args['subfields'], $args['label'], $field_prefix, $val);
		break;
		case 'address':
			launchpad_render_field_address($field_output_name, $field_output_id, $args, $val, $class, $subfield);
		break;
		case 'subfield':
			// SUBFIELDS ARE FOR SETTINGS ONLY!!!
			// DO NOT USE IN METABOXES!!!
			foreach($args['subfields'] as $field) {
				launchpad_render_form_field($field['args'], $field['name']);
			}
		break;
	}
	
	// Finally, if there is any "small" text associated with the field, handle the ouptput.
	if(isset($args['small'])) {
		// Decide if it should be block or inline based on the field type.
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
function launchpad_add_meta_boxes() {
	global $post;
	
	// Get all developer-manipulated post types.
	$post_types = launchpad_get_post_types();
	
	// If there aren't any, 
	if(!$post_types) {
		return;
	}
	
	// Loop the post types.
	foreach($post_types as $post_type => $post_type_details) {
		
		// If there are metabox keys, loop the metaboxes and add them.
		if(isset($post_type_details['metaboxes']) && $post_type_details['metaboxes']) {
			foreach($post_type_details['metaboxes'] as $metabox_id => $metabox_details) {
				$add_metabox = true;
				if(isset($metabox_details['limit'])) {
					$add_metabox = $metabox_details['limit']($post);
					if($add_metabox !== true && $add_metabox !== false) {
						$add_metabox = true;
					}
				}
				if($add_metabox) {
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
		}
		
		// If there are flexible content keys, loop the flexible content types and add metaboxes.
		if(isset($post_type_details['flexible']) && $post_type_details['flexible']) {
			foreach($post_type_details['flexible'] as $flex_id => $flex_details) {
				$add_metabox = true;
				if(isset($flex_details['limit'])) {
					$add_metabox = $flex_details['limit']($post);
					if($add_metabox !== true && $add_metabox !== false) {
						$add_metabox = true;
					}
				}
				if($add_metabox) {
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
}
if(is_admin()) {
	add_action('add_meta_boxes', 'launchpad_add_meta_boxes', 10, 1);
}


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
	
	// If there is no Launchpad fields, don't affect anything.
	if(empty($_POST) || !isset($_POST['launchpad_meta'])) {
		return;
	}
	
	// If the user can't edit, return.
	if($_POST['post_type'] === 'page') {
		if(!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if(!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	
	// Save each meta value.
	foreach($_POST['launchpad_meta'] as $meta_key => $meta_value) {
		update_post_meta($post_id, $meta_key, $meta_value);
	}
}
if(is_admin()) {
	add_action('save_post', 'launchpad_save_post_data');
}


/**
 * Meta Box Handler
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @uses		launchpad_render_form_field()
 * @since		1.0
 */
function launchpad_meta_box_handler($post, $args) {
	
	// Loop the fields that go into the metabox.
	foreach($args['args']['fields'] as $k => $v) {
		$add_metabox_field = true;
		if(isset($v['limit'])) {
			$add_metabox_field = $v['limit']($post);
			if($add_metabox_field !== true && $add_metabox_field !== false) {
				$add_metabox_field = true;
			}
		}
		if($add_metabox_field) {
	
		?>
		<div class="launchpad-metabox-field">
			<?php
			
			// Get the generic help for the type.
			$generic_help = launchpad_get_field_help($v['args']['type']);
			
			// If there is no specific field help, set it to empty.
			if(!isset($v['help'])) {
				$v['help'] = '';
			}
			
			// Add the generic help.
			$v['help'] .= $generic_help;
			
			// If there is any help related to the field, add the help hover tooltip.
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
			
			// Render the field.
			switch($v['args']['type']) {
				case 'address':
				case 'relationship':
				case 'repeater':
				case 'taxonomy':
				case 'wysiwyg':
				break;
				default:
					echo '<label>';
				break;
			}
				
			$v['args']['name'] = $k;
			
			// If there is a set value, override the developer specified value (if any).
			// This is used in the render form field output.
			if($post->$k && get_post_meta($post->ID, $k)) {
				$v['args']['value'] = get_post_meta($post->ID, $k, true);
			}
			
			// If this is not a checkbox, show the name before the field.
			if($v['args']['type'] !== 'checkbox' && $v['args']['type'] !== 'address') {
				echo $v['name']; 
			}
			
			// Render the form field.	
			launchpad_render_form_field($v['args'], false, 'launchpad_meta'); 
			
			// If this is a checkbox, show the name after the field.
			if($v['args']['type'] === 'checkbox') {
				echo $v['name']; 
			}
			
			switch($v['args']['type']) {
				case 'address':
				case 'relationship':
				case 'repeater':
				case 'taxonomy':
				case 'wysiwyg':
				break;
				default:
					echo '</label>';
				break;
			}
			
			?>
		</div>
	
		<?php
		}
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
	
	// Get the current post's post meta for the current field.
	$current_meta = get_post_meta($post->ID, $args['id'], true);
	
	// Render the flexible container.
	?>
		<div id="launchpad-flexible-container-<?php echo $args['id'] ?>" class="launchpad-flexible-container">
			<input type="hidden" name="launchpad_meta[<?php echo $args['id'] ?>]">
			<?php
			
			// If there are saved values in the current meta, send them to be rendered.
			if($current_meta) {
				// Loop the current meta.
				foreach($current_meta as $meta_k => $meta_v) {
					// Loop the fields in the meta.
					foreach($meta_v as $k => $v) {
						// Call the API for the flexible field
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
					
					// Loop all flexible modules so the user can pick them from a hover list.
					foreach($args['args']['modules'] as $k => $v) {
						$add_metabox = true;
						if(isset($v['limit'])) {
							$add_metabox = $v['limit']($post);
							if($add_metabox !== true && $add_metabox !== false) {
								$add_metabox = true;
							}
						}
						if($add_metabox) {
							echo '<li><a href="#" class="launchpad-flexible-link" data-launchpad-flexible-type="' . $args['id'] . '" data-launchpad-flexible-name="' . $k . '" data-launchpad-flexible-post-id="' . $post->ID . '" title="' . sanitize_text_field($v['help']) . '"><span class="' . ($v['icon'] ? $v['icon'] : 'dashicons dashicons-plus-alt') . '"></span> ' . $v['name'] . '</a></li>';
						}
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
 * @param		string|bool $type The flexible module key.
 * @param		string|bool $field_name The field name to use.
 * @param		int|bool $post_id The ID of the post to get existing values from.
 * @param		array $values The values to pre-populate.
 * @uses		launchpad_render_form_field()
 * @since		1.0
 */
function launchpad_get_flexible_field($type = false, $field_name = false, $post_id = false, $values = array()) {
	// The default assumption is that we are not on AJAX.
	$is_ajax = false;
	
	// If type is not set, assume this function was called via AJAX.
	if(!$type) {
		// Set the content type.
		header('Content-type: text/html');
		
		// Set AJAX to true to handle how the output works.
		$is_ajax = true;
		
		// Assign all parameters based off the GET parameters.
		$type = $_GET['type'];
		$name = $_GET['name'];
		$post_id = $_GET['id'];
		$field_name = $_GET['name'];
	}
	
	// Get all the launchpad post types.
	$post_types = launchpad_get_post_types();
	
	// Get the current post.
	$post = get_post($post_id);
	
	// If there are not post types, a post, or any fields in a flexible module, quit here.
	// This should not happen.
	if(
		!$post_types || 
		!$post || 
		!$post_types[$post->post_type] || 
		!$post_types[$post->post_type]['flexible'] ||
		!$post_types[$post->post_type]['flexible'][$type] || 
		!$post_types[$post->post_type]['flexible'][$type]['modules'] || 
		!$post_types[$post->post_type]['flexible'][$type]['modules'][$field_name]
	) {
		return '';
	}
	
	// Get the flexible field's details.
	$details = $post_types[$post->post_type]['flexible'][$type]['modules'][$field_name];
	
	// Start output buffering so we can capture it later.
	ob_start();
	
	// Print the container and the close link.
	echo '<div class="launchpad-flexible-metabox-container">';
	echo '<a href="#" onclick="jQuery(this).parent().remove(); return false;" class="launchpad-flexible-metabox-close">&times;</a>';
	
	// If there are help details for the module, include them here.
	if($details['help']) {
		?>
		<div class="launchpad-inline-help">
			<span>?</span>
			<div><?php echo $details['help']; ?></div>
		</div>
		<?php
	}
	
	// Output the sort handle and field name.
	echo '<div class="handlediv" onclick="jQuery(this).parent().toggleClass(\'closed\')"><br></div>';
	echo '<h3>' . $details['name'] . '</h3>';
	
	// Generate a unique ID for this flexible module to prevent collision.
	$flex_uid = preg_replace('/[^A-Za-z0-9\-\_]/', '', $field_name . '-' . uniqid());
	
	// Loop the field details.
	foreach($details['fields'] as $sub_field_name => $field) {
		// Assume we want a label.
		$use_label = true;
		
		// We don't want a label for these complex fields.
		switch($field['args']['type']) {
			case 'wysiwyg':
			case 'repeater':
				$use_label = false;
			break;
		}
		
		// Generate a unique ID for this field.
		$id = preg_replace('/[^A-Za-z0-9]/', '', $field_name . '' . $sub_field_name . '' . uniqid());
		
		// Print the field container.
		echo '<div class="launchpad-metabox-field">';
		
		// Get the help information for this field type.
		$generic_help = launchpad_get_field_help($field['args']['type']);
		
		// If the field doesn't have a help key, create it.
		if(!isset($field['help'])) {
			$field['help'] = '';
		}
		
		// Append generic help.
		$field['help'] .= $generic_help;
		
		// Set a temporary variable for help content.
		$help = $field['help'];
		
		// If the field is a repeater, we have to get the help info out of the sub-fields.
		if($field['args']['type'] === 'repeater') {
			$help .= '<p>Available fields:</p><dl>';
			
			// Loop the sub fields and build the help.
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
		
		// If we managed to create any help, create a help tooltip.
		if($help) {
			?>
			<div class="launchpad-inline-help">
				<span>?</span>
				<div><?php echo $help; ?></div>
			</div>
			<?php
		}
		
		// If we need a label, print it for the field.
		if($use_label && $field['args']['type'] !== 'checkbox') {
			echo '<label for="' . $id . '">' . $field['name'] . '</label>';
		} else if($use_label) {
			echo '<label for="' . $id . '">';
		}
		
		// If any values were passed, set them as an argument so they will be populated.
		if($values) {
			$field['args']['value'] = $values[$sub_field_name];
		}
		
		// Render the form field.
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
		
		if($use_label && $field['args']['type'] === 'checkbox') {
			echo $field['name'] . '</label>';
		}
		
		// Close the field container.
		echo '</div>';
		
	}
	
	// Close the module container.
	echo '</div>';
	
	// Get the buffer contents and clean the buffer.	
	$ret = ob_get_contents();
	ob_clean();
	
	// If this is AJAX, echo the content.
	if($is_ajax) {
		echo $ret;
		exit;
	
	// If not, return it because this was called as a function.
	} else {
		return $ret;
	}
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_get_flexible_field', 'launchpad_get_flexible_field');
	add_action('wp_ajax_nopriv_get_flexible_field', 'launchpad_get_flexible_field');
}


/**
 * Get Visual Editor Code
 *
 * @since		1.0
 */
function launchpad_get_editor() {
	// Generate the editor skeleton code.
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
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_get_editor', 'launchpad_get_editor');
	add_action('wp_ajax_nopriv_get_editor', 'launchpad_get_editor');
}


/**
 * AJAX Post Filter for Relationship Field
 *
 * @since		1.0
 */
function launchpad_get_post_list() {
	// JSON output header.
	header('Content-type: application/json');
	
	// Trim the requested search terms.
	$_GET['terms'] = trim($_GET['terms']);
	
	// If there are search terms, search for the terms.
	if($_GET['terms']) {
		$res = new WP_Query(
				array(
					'post_type' => $_GET['post_type'],
					's' => $_GET['terms']
				)
			);
	
	// If there are no terms, get the most recent 25 of post_type.
	} else {
		$res = new WP_Query(
				array(
					'post_type' => $_GET['post_type'],
					'posts_per_page' => 25
				)
			);
	}
	
	// Empty return array to populate.
	$ret = array();
	
	// Loop the post.
	foreach($res->posts as $p) {
		// Get the ancestors.
		$ancestors = get_post_ancestors($p);
		
		// If this is a child of something, we'll keep track in $small.
		$small = '';
		
		// If there are ancestors, reverse the order and append each title to $small.
		// This is used as a reference on the front end.
		if($ancestors) {
			$ancestors = array_reverse($ancestors);
			foreach($ancestors as $key => $ancestor) {
				$ancestor = get_post($ancestor);
				$small .= ($key > 0 ? ' » ' : '') . $ancestor->post_title;
			}
		}
		
		// Assing small to the ancestor_chain.
		$p->ancestor_chain = $small;
		// Add the post to the return variable.
		$ret[] = $p;
	}
	
	// Output the JSON.
	echo json_encode($ret);
	exit;
}
if($GLOBALS['pagenow'] === 'admin-ajax.php') {
	add_action('wp_ajax_search_posts', 'launchpad_get_post_list');
	add_action('wp_ajax_nopriv_search_posts', 'launchpad_get_post_list');
}
