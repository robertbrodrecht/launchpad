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
 * SEO Meta Box Handler
 *
 * @param		object $post The current post
 * @param		array $args Arguments passed from the metabox
 * @since		1.0
 */
function launchpad_seo_meta_box_handler($post, $args) {
	
	$stopwords = explode(',', "a,about,above,after,again,against,all,am,an,and,any,are,aren't,as,at,be,because,been,before,being,below,between,both,but,by,can't,cannot,could,couldn't,did,didn't,do,does,doesn't,doing,don't,down,during,each,few,for,from,further,had,hadn't,has,hasn't,have,haven't,having,he,he'd,he'll,he's,her,here,here's,hers,herself,him,himself,his,how,how's,i,i'd,i'll,i'm,i've,if,in,into,is,isn't,it,it's,its,itself,let's,me,more,most,mustn't,my,myself,no,nor,not,of,off,on,once,only,or,other,ought,our,ours,ourselves,out,over,own,same,shan't,she,she'd,she'll,she's,should,shouldn't,so,some,such,than,that,that's,the,their,theirs,them,themselves,then,there,there's,these,they,they'd,they'll,they're,they've,this,those,through,to,too,under,until,up,very,was,wasn't,we,we'd,we'll,we're,we've,were,weren't,what,what's,when,when's,where,where's,which,while,who,who's,whom,why,why's,with,won't,would,wouldn't,you,you'd,you'll,you're,you've,your,yours,yourself,yourselves");

	if($post->post_status === 'publish') {
		$full_content = file_get_contents(get_permalink($post->ID));
	
		preg_match_all('|<title>(.*?)</title>|s', $full_content, $title);
		
		if(isset($title[1][0])) {
			$title_natural = $title[1][0];
			$title = strtolower($title_natural);
		} else {
			$title = false;
		}
		
		$cont = strip_tags($full_content);
		$cont = preg_replace('|<script.*?>.*?</script>|s', '', $cont);
		$cont = preg_replace('|<style.*?>.*?</style>|s', '', $cont);
		$cont = preg_replace("/(\r\n|\r|\n)/", ' ', $cont);
		$cont = strtolower($cont);
		$cont = html_entity_decode($cont);
		
		foreach($stopwords as $stopword) {
			$cont = preg_replace('/\b' . $stopword . '\b/', '', $cont);
		}
		
		$cont = preg_replace('/\s+/', ' ', $cont);
		
		$word_count = str_word_count($cont);
	}

	$meta = get_post_meta($post->ID, 'SEO', true);
	
	$post_word_count = str_word_count(strip_tags($post->post_content));
	$seo_exerpt = launchpad_seo_excerpt(64, false, $post->ID);

	?>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>In order to run automated tests, you must enter your primary keyword / keyphrase that you want to target in your copy.</p>
				</div>
			</div>
			<label>
				Page Target Keyword / Keyphrase
				<input type="text" name="launchpad_meta[SEO][keyword]" value="<?php echo @$meta['keyword'] ?>">
			</label>
		</div>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>In some cases, you may want your page's title tag to contain specific keywords without having those keywords in the title of your page inside of WordPress.  If you like, enter a more SEO-friendly title here.  If you don't enter one, the page name will be used.  Here are some tips:</p>
					<ul>
						<li>Keep the title less than 70 characters.</li>
						<li>Put the primary keyword near the start of the title.</li>
						<li>Craft your title so that people want to click them.</li>
						<li>Try to make your title a call to action, a promise, or question that the page fulfills.</li>
						<li>Vary page titles.  Don't use the same page title on other pages.</li>
						<li>Don't use your page title in your SEO description.</li>
					</ul>
				</div>
			</div>
			<label>
				SEO'd Title
				<input type="text" name="launchpad_meta[SEO][title]" value="<?php echo @$meta['title'] ?>" maxlength="70">
			</label>
		</div>
		<div class="launchpad-metabox-field">
			<div class="launchpad-inline-help">
				<span>?</span>
				<div>
					<p>This field contains the meta description content.  Meta description is seen on SERPs (search engine results page) if the search query matches terms in the meta description.  A meta description should be a maximum of 160 characters as SERPs typically truncate the description to 160 characters.  As of 2009, Google does not use meta description in page rank algorithms.  If you do not enter a meta description, one will be automatically generated when the page loads based on either the first 32 words of the post, via the post excerpt, or via the text before the "more" tag.</p>
				</div>
			</div>
			<div>
			<label>
				SEO Description
				<textarea name="launchpad_meta[SEO][meta_description]" rows="10" cols="50" class="small-text" maxlength="160"><?php echo @substr($meta['meta_description'], 0 , 160) ?></textarea>
			</label>
			</div>
		</div>
		<div class="launchpad-serp-preview">
			<div class="launchpad-serp-heading"><?php echo substr($title_natural, 0 , 70) . (strlen($title_natural) > 70 ? '...' : ''); ?></div>
			<div class="launchpad-serp-url"><?php echo preg_replace('|^https?://|i','', get_permalink($post->ID)) ?></div>
			<div class="launchpad-serp-meta"><?php echo substr($seo_exerpt, 0 , 160) . (strlen($seo_exerpt) > 160 ? '...' : ''); ?></div>
		</div>
		<?php 
		
		if($post->post_status === 'publish' && trim($meta['keyword'])) {
			
			$keyword = trim($meta['keyword']);
			$keyword_orig = $keyword;
			
			$keyword = preg_replace("/(\r\n|\r|\n)/", ' ', $keyword);
			$keyword = preg_replace('/\s+/', ' ', $keyword);
			$keyword = strtolower($keyword);
			$keyword = html_entity_decode($keyword);
			
			$keyword_count = substr_count($cont, $keyword);
			
			$percent = round($keyword_count/$word_count*100, 2);

			$title_opt = false;
			
			if($title) {
				if(substr_count($title, $keyword)) {
					$title_opt = true;
				}
			}
					
		?>
		<dl class="launchpad-inline-listing">
			<dt>Keyword Count (Exact Matches)</dt>
			<dd>
				<?php echo $keyword_count; ?>
			</dd>
			<dt>Keyword Density (Exact Matches)</dt>
			<dd>
				<?php echo $percent ?>%
			</dd>
			<dt>Title</dt>
			<dd>
				<?php
					
					if(!$title) {
						echo 'Make sure you have a title on the page!!!';
					} else {
						if(strlen($title) > 70) {
							echo 'Consider shortening your title.';
						} else {
							$keyword_pos = stripos($title, $keyword);
							if($keyword_pos === 0) {
								echo 'Consider placing a word before your keyword.';
							} else if($keyword_pos === false) {
								echo 'Consider using this keyword in your title.';
							} else if($keyword_pos/strlen($title)*100 > 35) {
								echo 'Consider placing your keyword closer to the start of the title.';
							} else {
								echo 'No suggestions.';
							}
						}
					}
					
				?>
			</dd>
			<dt>Description</dt>
			<dd>
				<?php
				
					if(substr_count(strtolower($meta['meta_description']), strtolower($keyword))) {
						echo 'No suggestions.';
					} else {
						echo 'Consider using your keyword in the SEO description.';
					}
				
				?>
			</dd>
			<dt>Content</dt>
			<dd>
				<?php
				
					if($percent < .5) {
						echo 'Increase keyword usage to .5% to 2%.';
					} else if($percent < 2) {
						echo 'Acceptable keyword usage.';
					} else {
						echo 'Consider decreasing keyword usage to under 2%.';
					}
					
					if($post_word_count < 300) {
						echo ' Consider increasing your content length to 300+ words.  You currently have ' . $post_word_count . ' word' . ($post_word_count === 1 ? '' : 's') . ' in your main content (not accounting for flexible content modules).';
					}
				
				?>
			</dd>
		</dl>
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