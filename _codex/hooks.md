<center>[Previous](basic-template.md) | [Home](index.md) | [Next](sass.md)</center>

Launchpad Hooks
===============

Launchpad offers various filters to modify default values via WordPres's built-in <code>add_filter</code> to avoid modifying core Launchpad code.  These filters range from an effort to accomodate different file preferences to altering the behavior of Launchpad.

Several of the following filters are showin in lib/custom/examples.php if you need more specific guidance on how to use them.

## File Preferences

If you like to use a folder called "img" instead of "images", you'll need to add filters to the following.  Sample usage can be found in the examples file.  You can also use these to add custom rewite and cache paths.

<dl>
	<dt>launchpad_cache_manifest_file_paths</dt>
	<dd>Sends an array of the cache manifest paths that are searched for content.</dd>
	<dt>launchpad_rewrite_rules</dt>
	<dd>Sends an array of the rewrite rules.</dd>
</dl>


## Activation

If you want to use different settings for activation-related values (e.g. the path of news), these filters will allow you to do so.  Remember, you must add these filters **before** activating Launchpad.

<dl>
	<dt>launchpad_activate_home_name</dt>
	<dd>The post title of the home page as a string.  The slug is generated for you.</dd>
	<dt>launchpad_activate_articles_name</dt>
	<dd>The post title of the page to use as the posts page as a string.  The slug is generated for you.</dd>
	<dt>launchpad_activate_articles_path</dt>
	<dd>The permalink path for posts as a string.  This can be changed in Settings &gt; Permalinks after activation.  The fefault is <kbd>/articles/%postname%/</kbd></dd>
	<dt>launchpad_activate_upload_path</dt>
	<dd>The path for uploaded files as a string.  The default is <kbd>assets</kbd>.  See the examples file.</dd>
</dl>

## Basic Customizations

<dl>
	<dt>launchpad_image_sizes</dt>
	<dd>Sends an array of images sizes.  Launchpad offers a simplified array format to register image sizes.  To add an image size, append an array such as <code>array($image_name, $width, $height, $crop)</code>.  Sample code is in the examples file.</dd>
	<dt>launchpad_post_types</dt>
	<dd>An array of post types is sent.  There are samples in the example file and detailed a explanation in the [Post Types](post-types.md) section of this codex.</dd>
	<dt>launchpad_post_formats</dt>
	<dd>If you need access to post formats, include this filter and add to the array that is passed.  By default, post formats are disabled.</dd>
	<dt>launchpad_nav_menus</dt>
	<dd></dd>
	<dt>launchpad_custom_post_types</dt>
	<dd></dd>
</dl>

## Template Customization

launchpad_title
launchpad_excerpt
launchpad_modify_default_flexible_modules
launchpad_body_class

## Admin Modifications

launchpad_mce_style_formats
launchpad_setting_fields
launchpad_theme_options_page
launchpad_mce_style_formats
mce_buttons

## Advanced Backend Modifications

launchpad_cache_file_path