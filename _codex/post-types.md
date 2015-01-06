<center>[Previous](site-images.md) | [Home](index.md) | [Next](basic-template.md)</center>

Post Types, Taxonomies, Metaboxes, and Flexible Content in Launchpad
========================================================

Launchpad tries to make post types with taxonomies, metaboxes, and flexible content easy to add in code.  Simple post types have sane defaults or you can completely customize a post type, and have the settings passed directly to WordPress's <code>register_post_type</code>.  Metabox fields are stored as separate entries in WordPress post meta, and flexible content is stored as in a single post meta field that is automatically included in search.  Launchpad can even add metaboxes and flexible content to built-in posts and pages post types (see the Extending Built-in Types section below).  Launchpad also makes it easy to include help tooltips that tie into WordPress's help tabs.

Launchpad also ships with helpful flexible content modules with supporting CSS and JavaScript.

## Post Types

Here's an example of what you might use for a simple post type (we'll dissect it below):

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
function my_custom_post_types($post_types) {
	$custom_post_types = array(
		'sample_post_type' => array(
			'plural' => 'Samples',
			'single' => 'Sample',
			'slug' => 'samples',
			'help' => '<p>This is an example post type.</p>',
		)
	);
	return array_merge($post_types, $custom_post_types);
}
```

Post types are queued for registration by Launchpad's automated scripts by adding a filter on Launchpad's <code>custom_launchpad_custom_post_types</code>: 

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
```

The filter passes a single parameter, an array of the post types Launchpad will register.  You should merge your array with the passed array since Launchpad may one day include built-in post types:

```php
return array_merge($post_types, $custom_post_types);
```

The Launchpad post types array uses a key that represents the post type (the first parameter of <code>register_post_type</code>) and an array as the value.  The value array should contain, at minimum, key / values for:

<dl>
	<dt>plural</dt>
	<dd>The plural name of the post type.</dd>
	<dt>single</dt>
	<dd>The singular name of the post type.</dd>
	<dt>slug</dt>
	<dd>The slug of the post type for rewrites.  If you specify false, the post type becomes "private."  That is, no rewrite, not publically query-able, and not public.</dd>
</dl>

The single and plural keys are used to populate the various <code>labels</code> values.  All other values sent to <code>register_post_type</code> will be created from default values.

The following key / values are optional:

<dl>
	<dt>help</dt>
	<dd>Information for the user that appears in the "Help" tab.</dd>
	<dt>hierarchical</dt>
	<dd>A boolean of whether the post should be hierarchical.  The default is <code>false</code>.</dd>
	<dt>supports</dt>
	<dd>An array of WordPress features that the post type should support. The default is: title, editor, and thumbnail</dd>
	<dt>menu_icon</dt>
	<dd>An option dashicon or URL as described in the [WordPress documentation](http://codex.wordpress.org/Function_Reference/register_post_type).</dd>
</dl>

If your post type is more complex than these fields allow, you will need to send a complete array as specified by WordPress's [<code>register_post_type</code>](http://codex.wordpress.org/Function_Reference/register_post_type).  Simple include a <code>labels</code> key in your array to trigger bybass the automatic post type builder and have your raw array sent to <code>register_post_type</code>.

In the event that you want to add taxonomies, metaboxes, or flexible content to a built-in post type (i.e. post and page), you can include a key / value for those types.  If you include any of the above settings for the post or page post type, those settings will be ignored.  You may only extend the existing post types with taxonomies, metaboxes, and flexible content via Launchpad's automatic post type handling.

## Taxonomies

Like you'll see with metaboxes and flexible content, adding taxonomies to post types is simply adding a key to your post type array called "taxonomies" with key / value arrays of each taxonomy.  For example, we can build on top of our example above:

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
function my_custom_post_types($post_types) {
	$custom_post_types = array(
		'sample_post_type' => array(
			'plural' => 'Samples',
			'single' => 'Sample',
			'slug' => 'samples',
			'help' => '<p>This is an example post type.</p>',
			'taxonomies' => array(
				'launchpad_sample_tax' => array(
						'plural' => 'Sample Taxonomies',
						'single' => 'Sample Taxonomy',
						'slug' => 'sample_taxonomy'
					)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
```

The plural, single, and slug are used to extrapolate settings to send to <code>register_taxonomy</code>.  The taxonomy is already directly correlated to the custom post type.  If you need more fine-grained control of taxonomy settings or you need to apply a single taxonomy to multiple post types, pass your <code>register_taxonomy</code>-compliant array with a <code>label</code> key to pass the array directly to [<code>register_taxonomy</code>](http://codex.wordpress.org/Function_Reference/register_taxonomy).

## Metaboxes

Metaboxes, like taxonomies, are created with an array of arrays created under the key <code>metaboxes</code>.  In that array are key / value pairs that represent the metabox ID and the metabox details, respectively.  Each array of metabox details should include: 

<dl>
	<dt>name</dt>
	<dd>The title of the metabox.</dd>
	<dt>location</dt>
	<dd>The location (or context, as WP calls it) of the metabox.  WordPress allows: 'normal', 'advanced', or 'side'.  If you use 'advanced,' Launchpad will hoist the fields above the visual editor.</dd>
	<dt>position</dt>
	<dd>The position within the position (or priority, as WP calls it): 'high', 'core', 'default' or 'low'</dd>
	<dt>help</dt>
	<dd>A description of what the field does.</dd>
	<dt>limit</dt>
	<dd>An anonymous function that accepts a post as the only argument.  This is used to decide whether the metabox will appear on the post's edit screen.  This is discussed in detail below in the Limiting Metaboxes and Flexible Content section.</dd>
	<dt>fields</dt>
	<dd>An array of fields to include.  This will be discussed in detail below in the Field Types section.</dd>
</dl>

If we extend our running example, it might look like this:

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
function my_custom_post_types($post_types) {
	$custom_post_types = array(
		'sample_post_type' => array(
			'plural' => 'Samples',
			'single' => 'Sample',
			'slug' => 'samples',
			'help' => '<p>This is an example post type.</p>',
			'taxonomies' => array(
				'launchpad_sample_tax' => array(
						'plural' => 'Sample Taxonomies',
						'single' => 'Sample Taxonomy',
						'slug' => 'sample_taxonomy'
					)
			),
			'metaboxes' => array(
				'custom_metabox_id' => array(
					'name' => 'Sample Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>Help about what this metabox does.</p>',
					'fields' => array(
						'custom_metabox_field' => array(
							'name' => 'Title',
							'help' => '<p>Help about what this field does.</p>',
							'args' => array(
								'type' => 'text'
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

## Flexible Content

Flexible Content allows the WordPress user to easily create varying layouts with different content layouts with the click of a button.  Launchpad has bult-in support for serveral flexible content types: Accordion List, Link List, Section Navigation, and additional visual editors via Simple Content.  You can add your own flexible content modules in much the same way you add custom metaboxes via the <code>flexible</code> key in the post type array that contains an array of arrays.  Much like the metaboxes array, the key is the flexible content ID and the array contains the configuration details.  Each set of flexible content modules contains the following keys:


<dl>
	<dt>name</dt>
	<dd>The title of the flexible content module.</dd>
	<dt>location</dt>
	<dd>The location (or context, as WP calls it) of the metabox.  WordPress allows: 'normal', 'advanced', or 'side'</dd>
	<dt>position</dt>
	<dd>The position within the position (or priority, as WP calls it): 'high', 'core', 'default' or 'low'</dd>
	<dt>help</dt>
	<dd>A description of what the flexible content area is used for.</dd>
	<dt>limit</dt>
	<dd>An anonymous function that accepts a post as the only argument.  This is used to decide whether the flexible content block will appear on the post's edit screen.  This is discussed in detail below in the Limiting Metaboxes and Flexible Content section.</dd>
	<dt>modules</dt>
	<dd>
		An array of modules to include.  Modules are essentially metaboxes and use a similar array format where the key is the ID of the module and the value is an array of settings.  The modules array contains the following keys: 
		<dl>
			<dt>name</dt>
			<dd>The name of the module.</dd>
			<dt>icon</dt>
			<dd>Preferably a [dashicon](https://github.com/melchoyce/dashicons) such as <code>dashicons dashicons-list-view</code>, but can be a custom icon class.</dd>
			<dt>help</dt>
			<dd>A description of what the module does.</dd>
			<dt>limit</dt>
			<dd>An anonymous function that accepts a post as the only argument.  This is used to decide whether the flexible content module will appear on the post's edit screen.  This is discussed in detail below in the Limiting Metaboxes and Flexible Content section.</dd>
			<dt>fields</dt>
			<dd>An array of fields to include.  This will be discussed in detail below in the Field Types section.</dd>
		</dl>
	</dd>
</dl>

To continue expanding our example, adding a custom flexible content module in addition to the built-in custom content modules may work like this:

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
function my_custom_post_types($post_types) {

	$built_in_flexible = launchpad_get_default_flexible_modules();
	$custom_flexible = array(
		'custom_content_editor' => array(
			'name' => 'Custom Content Editor',
			'icon' => 'dashicons dashicons-admin-page',
			'help' => '<p>Just a simple editor.</p>',
			'fields' => array(
				'description' => array(
					'name' => 'Accordion Description',
					'help' => '<p>An editor for you to use.</p>',
					'args' => array(
						'type' => 'wysiwyg'
					)
				)
			)
		)
	);
	
	$custom_post_types = array(
		'sample_post_type' => array(
			'plural' => 'Samples',
			'single' => 'Sample',
			'slug' => 'samples',
			'help' => '<p>This is an example post type.</p>',
			'taxonomies' => array(
				'launchpad_sample_tax' => array(
						'plural' => 'Sample Taxonomies',
						'single' => 'Sample Taxonomy',
						'slug' => 'sample_taxonomy'
					)
			),
			'metaboxes' => array(
				'custom_metabox_id' => array(
					'name' => 'Sample Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>Help about what this metabox does.</p>',
					'fields' => array(
						'custom_metabox_field' => array(
							'name' => 'Title',
							'help' => '<p>Help about what this field does.</p>',
							'args' => array(
								'type' => 'text'
							)
						)
					)
				)
			),
			'flexible' => array(
				'page_flexible' => array(
					'name' => 'Page Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					'modules' => array_merge($built_in_flexible, $custom_flexible)
				)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
```

## Field Types

Metaboxes and Flexible Content use the field arrays to define what fields are available to the user.  The field array is an array of arrays where the key is the field name and the value is the settings for the field.  Launchpad supports many different field types, but the array format is the same.  A field contains the following keys:

<dl>
	<dt>name</dt>
	<dd>The label for the field.</dd>
	<dt>help</dt>
	<dd>A discription of what the field does.</dd>
	<dt>args</dt>
	<dd>
		The arguments for the field.  This value is an array with keys / values that control certain aspects of the field.  The keys are:
		<dl>
			<dt>type</dt>
			<dd>The type of field to display.  See below for specific types that are available.</dd>
			<dt>default</dt>
			<dd>The default value of the field.</dd>
			<dt>options</dt>
			<dd>For select and selectmulti, options is a key / value array that translates to: <code>&lt;option value="key"&gt;value&lt;/option&gt;</code>.  You can specify an array of arrays to include an optgroup where the key is the <code>optgroup</code>'s <code>label</code> and the value is a key / value array to specify the options in the optgroup.</dd>
			<dt>maxlength</dt>
			<dd>For text and textarea fields, the maximum number of characters that can go in the field.</dd>
			<dt>post_type</dt>
			<dd>For relationship fields, the post type to use to populate the list.</dd>
			<dt>query</dt>
			<dd>For relationship fields, any extra WP_Query parameters that you want to merge into the query that is automatically generated by post_type.  This array is the second parameter in an <code>array_merge</code></dd>
			<dt>limit</dt>
			<dd>For relationship fields, the total number of posts that can be attached.  For file fields, specify a string of the type of file that can be attached.  As of the time of this writing, I can't find detailed documentation on what types WordPress supports, but there seems to be: image, video, audio.</dd>
			<dt>taxonomy</dt>
			<dd>For taxonomy fields, the taxonomy slug to populate the taxonomy list.</dd>
			<dt>multiple</dt>
			<dd>For taxonomy fields, whether multiple taxonomies can be selected.<dd>
			<dt>label</dt>
			<dd>For repeater fields, the label above the repeater. For address fields, the legend of the fieldset containing the address.</dd>
			<dt>subfields</dt>
			<dd>For repeater fields, the subfields array is the same as the fields array.</dd>
		</dl>
	</dd>
</dl>

The following field types are available:

<dl>
	<dt>address</dt>
	<dd>A complex field to contain street address, suite number, city, state, and zip with automatic back-end geocoding if you have provided a Google Maps API key in Launchpad Setting.</dd>
	<dt>checkbox</dt>
	<dd>An HTML checkbox field.</dd>
	<dt>date</dt>
	<dd>An HTML type="date" that reveals a jQuery date picker.</dd>
	<dt>file</dt>
	<dd>A WordPress file picker to select a single file.</dd>
	<dt>menu</dt>
	<dd>A drop down to select a menu.</dd>
	<dt>relationship</dt>
	<dd>A field that allows the user to select one or more of a particular post type.</dd>
	<dt>repeater</dt>
	<dd>A field group that allows the user to add multiple sets of specific fields.</dd>
	<dt>select</dt>
	<dd>An HTML select element.</dd>
	<dt>selectmulti</dt>
	<dd>An HTML select multiple element.</dd>
	<dt>taxonomy</dt>
	<dd>A select field populated with a specific taxonomy's terms.</dd>
	<dt>text</dt>
	<dd>An HTML text input.</dd>
	<dt>textarea</dt>
	<dd>An HTML textarea.</dd>
	<dt>wysiwyg</dt>
	<dd>A WordPress editor, complete with visual and text only views and media buttons.</dd>
</dl>

For a complete example of all the field types, look at lib/custom/examples.php.

## Extending Built-in Types

If you would like to extend the pages or posts built-in post types, you simply need to include the keys in your post type array with the additions you would like to include.  Since WordPress already has the post type registered, you'll only need to add the specific <code>taxonomies</code>, <code>metaboxes</code>, and <code>flexible</code> keys.  For example:

```php
add_filter('launchpad_custom_post_types', 'my_custom_post_types');
function my_custom_post_types($post_types) {

	$built_in_flexible = launchpad_get_default_flexible_modules();
	$custom_flexible = array(
		'custom_content_editor' => array(
			'name' => 'Custom Content Editor',
			'icon' => 'dashicons dashicons-admin-page',
			'help' => '<p>Just a simple editor.</p>',
			'fields' => array(
				'description' => array(
					'name' => 'Accordion Description',
					'help' => '<p>An editor for you to use.</p>',
					'args' => array(
						'type' => 'wysiwyg'
					)
				)
			)
		)
	);
	
	$custom_post_types = array(
		'page' => array(
			'taxonomies' => array(
				'launchpad_sample_tax' => array(
						'plural' => 'Sample Taxonomies',
						'single' => 'Sample Taxonomy',
						'slug' => 'sample_taxonomy'
					)
			),
			'metaboxes' => array(
				'custom_metabox_id' => array(
					'name' => 'Sample Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>Help about what this metabox does.</p>',
					'fields' => array(
						'custom_metabox_field' => array(
							'name' => 'Title',
							'help' => '<p>Help about what this field does.</p>',
							'args' => array(
								'type' => 'text'
							)
						)
					)
				)
			),
			'flexible' => array(
				'page_flexible' => array(
					'name' => 'Page Flexible Content',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>The sample flexible content is designed to help you build your own flexible content.</p>',
					'modules' => array_merge($built_in_flexible, $custom_flexible)
				)
			)
		)
	);
	return array_merge($post_types, $custom_post_types);
}
```

## Limiting Metaboxes and Flexible Content

With Launchpad 1.1 you can now limit where metaboxes and flexible content appear.  For example, if you only want a post with a specific ID to recieve a specific metabox, that is now possible through an anonymous function (introduced in PHP 5.3).  For example, if you only want a metabox to appear on the Home page, add a key to your metabox called "limit" with a value that is an anonymous function that does the check you need:

```php
	$custom_post_types = array(
		'page' => array(
			'metaboxes' => array(
				'custom_metabox_id' => array(
					'name' => 'Sample Metabox',
					'location' => 'normal',
					'position' => 'default',
					'help' => '<p>Help about what this metabox does.</p>',
					'limit' => function($post) {
						if($post->post_name === 'home') {
							return true;
						}
						return false;
					},
					'fields' => array(
						'custom_metabox_field' => array(
							'name' => 'Title',
							'help' => '<p>Help about what this field does.</p>',
							'args' => array(
								'type' => 'text'
							)
						)
					)
				)
			)
		)
	);
```

Since you are passed the post object, you can evaluate the post object or get specific information about the post (e.g. is it in a specific category?) to apply your own filtering that you write yourself.  Simply return <code>true</code> to allow the metabox, flexible content block, or flexible content module to appear for the post.  Return <code>false</code> to prevent it from appearing.  If you return anything other than a boolean <code>true</code> or <code>false</code> the metabox, flexible content block. or flexible content module will appear.

With a basic understanding of managing custom post types, you may want to read more about [Basic Template Editing](basic-template.md).