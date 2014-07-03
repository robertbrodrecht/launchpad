<center>[Previous](site-images.md) | [Home](index.md) | Next</center>

Post Types, Metaboxes, and Flexible Content in Launchpad
========================================================

Launchpad tries to make post types with taxonomies, metaboxes, and flexible content easy to add in code.  Simple post types have sane defaults or you can completely customize a post type, and have the settings passed directly to WordPress's <code>register_post_type</code>.  Metabox fields are stored as separate entries in WordPress post meta, and flexible content is stored as in a single post meta field that is automatically included in search.  Launchpad can even add metaboxes and flexible content to built-in posts and pages post types.  Launchpad also makes it easy to include help tooltips that tie into WordPress's help tabs.

Launchpad also ships with helpful flexible content modules with supporting CSS and JavaScript.

Here's an example of what you might use for a simple post type (we'll dissect it below):

```php
add_filter('launchpad_custom_post_types', 'custom_launchpad_custom_post_types');
function my_custom_post_types($post_types) {
	$custom_post_types = array(
		'page' => array(
			'flexible' => array(
					'page_flexible' => array(
						'name' => 'Page Flexible Content',
						'location' => 'normal',
						'position' => 'default',
						'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
						'modules' => launchpad_get_default_flexible_modules()
					)
				)
		),
		'sample_post_type' => array(
			'plural' => 'Samples',
			'single' => 'Sample',
			'slug' => 'samples',
			'help' => '<p>This is a sample post type designed to help you see how things work.</p>',
			'menu_position' => null,
			// hierarchical is not required.  default: false.
			'hierarchical' => true,
			// supports is not required.  default: title, editor, thumbnail.
			'supports' => array(
					'title',
					'editor',
					'thumbnail',
					'page-attributes'
				),
			// taxonomies is not required.
			'taxonomies' => array(
					'launchpad_sample_tax' => array(
							'plural' => 'Sample Taxonomies',
							'single' => 'Sample Taxonomy',
							'slug' => 'sample_taxonomy'
						)
				),
			// metaboxes is not required.
			'metaboxes' => array(
				'launchpad_sample_side_metabox' => array(
					'name' => 'Sample Side Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample metabox is designed to help you build your own metaboxes.</p>',
					'fields' => array(
						'sample_side_metabox_value_text' => array(
							'name' => 'Text',
							'help' => '<p>This field contains sample text.</p>',
							'args' => array(
								'type' => 'text'
							)
						)
					)
				)
			),
			// flexible is not required.
			'flexible' => array(
				'page_flexible' => array(
					'name' => 'Page Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					'modules' => array(
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
						)
					)
				)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
```

Post types are queued for registration by Launchpad's automated scripts by adding a filter on Launchpad's <code>custom_launchpad_custom_post_types</code>: 

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
```

The filter passes a single parameter, an array of the post types Launchpad will register.