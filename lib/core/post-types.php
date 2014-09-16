<?php

/**
 * WordPress Post Types
 *
 * Creation and low-level support of custom post types.
 *
 * @package 	Launchpad
 * @since		1.0
 */


/**
 * Get Post Types
 * 
 * @since		1.0
 */
function launchpad_get_post_types() {
	// By default, there are no custom post types.
	$post_types = array();
	
	// Apply filters to allow the developer to add post types.
	$post_types = apply_filters('launchpad_custom_post_types', $post_types);
	
	// Return the post types.
	return $post_types;
}


/**
 * Register Post Types
 * 
 * Register post types based on an array of post type details.
 * If the details of either the post type or taxonomy includes a labels key
 * the script assumes that the user has specified a real post type arguments
 * array and will use that as is instead of trying to set up a generic one.
 *
 * @since		1.0
 */
function launchpad_register_post_types() {
	// Get the available post types provided by WordPress.
	$registered_post_types = get_post_types();
	
	// Get post types and modifications created by the developer.
	$post_types = launchpad_get_post_types();
	
	// If the developer didn't make any, return here.
	if(!$post_types) {
		return;
	}
	
	// Loop the developer-related post types and modifications.
	foreach($post_types as $post_type => $post_type_details) {
		
		// If the current post type has custom taxonomies, put them in an array.
		if(isset($post_type_details['taxonomies']) && $post_type_details['taxonomies'] && is_array($post_type_details['taxonomies'])) {
			$taxonomies = $post_type_details['taxonomies'];
			
		// Otherwise, set the list to false.
		} else {
			$taxonomies = false;
		}
		
		// Remove the taxonomies in case they are applied directly to register_post_type.
		unset($post_type_details['taxonomies']);
		
		// If the developer sets a 'labels' key, it means the developer wants full control of the registration array.
		if(isset($post_type_details['labels'])) {
			$args = $post_type_details;
		
		// Otherwise, we need to create an array to send to register_post_type.
		} else if(isset($post_type_details['plural'])) {
		
			// Grab the values set by the developer.
			$post_type_plural = $post_type_details['plural'];
			if(isset($post_type_details['single'])) {
				$post_type_single = $post_type_details['single'];
			} else {
				$post_type_single = $post_type_details['plural'] . 's';
			}
			if(isset($post_type_details['slug'])) {
				$post_type_slug = $post_type_details['slug'];
			} else {
				$post_type_slug = false;
			}
			if(isset($post_type_details['menu_position'])) {
				$post_type_menu_options = $post_type_details['menu_position'];
			} else {
				$post_type_menu_options = null;
			}
			if(isset($post_type_details['menu_icon'])) {
				$post_type_menu_icon = $post_type_details['menu_icon'];				
			} else {
				$post_type_menu_icon = false;
			}
			if(isset($post_type_details['show_in_menu'])) {
				$post_type_show_in_menu = $post_type_details['show_in_menu'];				
			} else {
				$post_type_show_in_menu = true;
			}
			
			// If the developer set 'supports' values, use those.
			if(isset($post_type_details['supports'])) {
				$supports = $post_type_details['supports'];
			
			// If not, use the defaults.
			} else {
				$supports = array(
						'title',
						'editor',
						'thumbnail'
					);
			}
			
			// Apply the default values to the array.
			$args = array(
				'labels' => array(
						'name' => $post_type_plural,
						'singular_name' => $post_type_single,
						'add_new' => 'Add New ' . $post_type_single,
						'add_new_item' => 'Add ' . $post_type_single,
						'edit_item' => 'Edit ' . $post_type_single,
						'new_item' => 'New ' . $post_type_single,
						'all_items' => 'All ' . $post_type_plural,
						'view_item' => 'View ' . $post_type_single,
						'search_items' => 'Search ' . $post_type_plural,
						'not_found' =>  'No ' . strtolower($post_type_plural) . ' found',
						'not_found_in_trash' => 'No ' . strtolower($post_type_plural) . ' found in Trash', 
						'parent_item_colon' => '',
						'menu_name' => $post_type_plural
					),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => $post_type_show_in_menu, 
				'show_in_menu' => true, 
				'query_var' => true,
				'rewrite' => array(
						'slug' => $post_type_slug,
						'with_front' => false
					),
				'capability_type' => 'page',
				'has_archive' => true,
				'hierarchical' => isset($post_type_details['hierarchical']) ? (bool) $post_type_details['hierarchical'] : false,
				'menu_position' => $post_type_menu_options,
				'supports' => $supports
			);
			
					
			if($post_type_menu_icon) {
				$args['menu_icon'] = $post_type_menu_icon;
			}
			
			// If the post type is false, make the post type "private."			
			if(!$post_type_slug) {
				$args['publicly_queryable'] = false;
				$args['rewrite'] = false;
			}
		}
		
		// If the post type is not a built-in post type, register it.
		if(!in_array($post_type, $registered_post_types)) {
			register_post_type($post_type, $args);
		}
		
		// If the developer included taxonomies, deal with them.
		if($taxonomies) {
			
			// Loop the taxonomies.
			foreach($taxonomies as $taxonomy => $taxonomy_details) {
				// If the developer included 'labels', they want full control.
				if($taxonomy_details['labels']) {
					register_taxonomy($taxonomy_details);
				
				// Otherwise, we have to build an array for the developer.
				} else {
					// Set the defaults.
					$taxonomy_plural = $taxonomy_details['plural'];
					$taxonomy_single = $taxonomy_details['single'];
					$taxonomy_slug = $taxonomy_details['slug'];
					
					// Register the taxonomy for the current post type.
					register_taxonomy(
							$taxonomy, 
							array($post_type), 
							array(
								'labels' => array(
									'name' => $taxonomy_plural,
									'singular_name' => $taxonomy_single,
									'search_items' => 'Search ' . $taxonomy_plural,
									'all_items' => 'All ' . $taxonomy_plural,
									'parent_item' => 'Parent ' . $taxonomy_single,
									'parent_item_colon' => 'Parent ' . $taxonomy_single . ':',
									'edit_item' => 'Edit ' . $taxonomy_single,
									'update_item' => 'Update ' . $taxonomy_single,
									'add_new_item' => 'Add New ' . $taxonomy_single,
									'new_item_name' => 'New ' . $taxonomy_single,
									'menu_name' => $taxonomy_plural
								),
								'query_var' => $taxonomy_slug,
								'hierarchical' => true,
								'rewrite' => array(
									'slug' => $taxonomy_slug, 
									'hierarchical' => false, 
									'with_front' => false
								)
							)
						);
				}
			}
		}
	}	

}
add_action('init', 'launchpad_register_post_types');
