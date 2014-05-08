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
	
	$registered_post_types = get_post_types();
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
		
		if(!in_array($post_type, $registered_post_types)) {
			register_post_type($post_type, $args);
		}
		
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
			
			if($v['help']) {
				?>
				<div class="launchpad-inline-help">
					<span>?</span>
					<div><?php echo $v['help']; ?></div>
				</div>
				<?php
			}
			
			?>
			<label>
				<?php 
					
					echo $v['name']; 
					$v['args']['name'] = $k;
					
					if($post->$k && get_post_meta($post->ID, $k)) {
						$v['args']['value'] = $post->$k;
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
			
			?>
		</div>
		<div class="launchpad-flexible-add">
			<div>
				<button type="button" class="button">Add Content Module</button>
				<ul>
					<?php
					
					foreach($args['args']['modules'] as $k => $v) {
						echo '<li><a href="#" class="launchpad-flexible-link" data-launchpad-flexible-type="' . $args['id'] . '" data-launchpad-flexible-name="' . $k . '" data-launchpad-flexible-post-id="' . $post->ID . '" title="' . sanitize_text_field($v['help']) . '">' . $v['name'] . '</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	<?php
}


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
					'id' => $post_type . '-luanchpad_help',
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
							'id' => $post_type . '-' . $metabox_key . '-luanchpad_help',
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
							'id' => $post_type . '-' . $flex_key . '-luanchpad_help',
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
 * Default Flexible Modules
 * 
 * @since		1.0
 */
function launchpad_get_default_flexible_modules() {
	$return = array(
		'simple_content' => array(
			'name' => 'Simple Content',
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
		)
	);
	
	$return = apply_filters('launchpad_modify_default_flexible_modules', $return);
	
	return $return;
}