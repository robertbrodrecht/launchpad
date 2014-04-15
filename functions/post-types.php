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
	$post_types = array();
	$post_types = apply_filters('launchpad_custom_post_types', $post_types);
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
	
	$post_types = launchpad_get_post_types();
	
	if(!$post_types) {
		return;
	}
			
	foreach($post_types as $post_type => $post_type_details) {
		if($post_type_details['taxonomies'] && is_array($post_type_details['taxonomies'])) {
			$taxonomies = $post_type_details['taxonomies'];
		} else {
			$taxonomies = false;
		}
		unset($post_type_details['taxonomies']);
		
		if($post_type_details['labels']) {
			$args = $post_type_details;
		} else {
			$post_type_plural = $post_type_details['plural'];
			$post_type_single = $post_type_details['single'];
			$post_type_slug = $post_type_details['slug'];
			$post_type_menu_options = $post_type_details['menu_position'];
			
			if($post_type_details['supports']) {
				$supports = $post_type_details['supports'];
			} else {
				$supports = array(
						'title',
						'editor',
						'thumbnail'
					);
			}
			
			
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
				'show_ui' => true, 
				'show_in_menu' => true, 
				'query_var' => true,
				'rewrite' => array(
						'slug' => $post_type_slug,
						'with_front' => false
					),
				'capability_type' => 'page',
				'has_archive' => true,
				'hierarchical' => (bool) $post_type_details['hierarchical'],
				'menu_position' => $post_type_menu_options,
				'supports' => $supports
			);
		}
	
		register_post_type($post_type, $args);
		
		if($taxonomies) {
			foreach($taxonomies as $taxonomy => $taxonomy_details) {
				if($taxonomy_details['labels']) {
					register_taxonomy($taxonomy_details);
				} else {
					$taxonomy_plural = $taxonomy_details['plural'];
					$taxonomy_single = $taxonomy_details['single'];
					$taxonomy_slug = $taxonomy_details['slug'];
					
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


/**
 * Add Post Type Meta Boxes
 *
 * @since		1.0
 */
function launchpad_add_meta_boxs() {
	$post_types = launchpad_get_post_types();
	
	if(!$post_types) {
		return;
	}
	
	foreach($post_types as $post_type => $post_type_details) {
		if(isset($post_type_details['metaboxes'])) {
			foreach($post_type_details['metaboxes'] as $metabox_id => $metabox_details) {
				// A sample metabox registration
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
	
}
add_action('add_meta_boxes', 'launchpad_add_meta_boxs', 10, 1);


/**
 * Add Sample Meta Boxes
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @since		1.0
 */
function launchpad_meta_box_handler($post, $args) {
	
	foreach($args['args']['fields'] as $k => $v) {
		?>
		<div class="launchpad-metabox-field">
			<label>
				<?php 
					
					echo $v['name']; 
					$v['args']['name'] = $k;
					
					if($post->$k && get_post_meta($post->ID, $k)) {
						$v['args']['value'] = $post->$k;
					}
					
				?>
				<?php launchpad_render_settings_field($v['args'], false, 'launchpad_meta'); ?>
			</label>
		</div>
	
		<?php
	}
}

